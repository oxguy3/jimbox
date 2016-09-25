<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Letter;
use AppBundle\Form\LetterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/index.html.twig', array());
    }

    /**
     * @Route("/letters/new", name="letters_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function lettersNewAction(Request $request)
    {
        // just setup a fresh $task object (remove the dummy data)
        $letter = new Letter();

        $form = $this->createForm(LetterType::class, $letter);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $letter = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $em = $this->getDoctrine()->getManager();
            $em->persist($letter);
            $em->flush();

            return new Response("<html><body><h1>it's done!</h1><p><a href='/letters'>click here</a> (this page will be less ugly later, i promise)</p></body></html>");
            //return $this->redirectToRoute('task_success');
        }

        return $this->render('default/letters_new.html.twig', array(
            'letterForm' => $form->createView(),
        ));
    }

    /**
     * @Route("/letters", name="letters_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function lettersListAction(Request $request)
    {
//        $repo = $this->getDoctrine()->getRepository('AppBundle:Letter');
//        $query = $repo->createQueryBuilder('l')
//            ->orderBy('l.nameLast', 'ASC')
//            ->getQuery();
//        $letters = $query->getResult();

        return $this->render('default/letters_list.html.twig');
    }

    /**
     * @Route("/api/letters", name="api_letters")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function lettersListJsonAction(Request $request)
    {
//        print_r($request);
//        return new Response("butts");

        // error message to pass to DataTables
        $error = '';

        // start a query builder
        $repo = $this->getDoctrine()->getRepository('AppBundle:Letter');
        $qb = $repo->createQueryBuilder('l');

        // implement searches for each column
        $columns = $request->get('columns', []);
        if (!is_array($columns)) {
            return $this->generateDataTablesError("Missing/invalid columns array.");
        }
        $dummyLetter = new Letter();

        $index = 0;
        foreach ($columns as $key=>$value) {
            // check that their array is a consecutively-numbered non-associative array
            if (intval($key) !== $index++) {
                return $this->generateDataTablesError("Columns array is not sequentially indexed.");
            }

            // check that they gave us a legitimate column name
            $colName = $value['data'];
            if (!array_key_exists($colName, $dummyLetter->jsonSerialize())) {
                return $this->generateDataTablesError("Invalid column name: $colName.");
            }

            // I ain't implementing regex, so make sure they aren't expecting me to
            if ($value['search']['regex'] != 'false') {
                return $this->generateDataTablesError("Regex is not supported for column searches.");
            }

            // add a WHERE clause for their search query (if they have one)
            $search = $value['search']['value'];
            if ($search != null && $search != '') {
                $qb->andWhere("l.$colName LIKE ?$index");
                $qb->setParameter($index, "%$search%");
            }
        }

        // implement ordering
        $order = $request->get('order');
        $hasResetPreviousOrdering = false;
        foreach ($order as $o) {
            $colName = 'l.'.$columns[intval($o['column'])]['data'];
            $dir     = $o['dir'] == "asc" ? "ASC" : "DESC";

            // we need to use orderBy instead of addOrderBy for the first order clause, to remove default ordering
            if (!$hasResetPreviousOrdering) {
                $qb->orderBy($colName, $dir);
                $hasResetPreviousOrdering = true;
            } else {
                $qb->addOrderBy($colName, $dir);
            }

        }

        // TODO: Implement multi-column searching

        // get the count of rows for this filtered query
        $qb->select($qb->expr()->count('l.id'));
        $countFiltered = intval($qb->getQuery()->getSingleScalarResult());

        // change the SELECT from "count(id)" back to "*", then add offset and limit (AKA first and max)
        $qb->select('l');
        $qb->setFirstResult(intval($request->get('start')));
        $qb->setMaxResults(intval($request->get('length')));

        // get the results for this query
        $letters = $qb->getQuery()->getResult();

        // use a new query to get count of all rows (without filtering)
        $qbAll = $repo->createQueryBuilder('l');
        $qbAll->select($qbAll->expr()->count('l.id'));
        $countAll = intval($qbAll->getQuery()->getSingleScalarResult());

        // build the response object
        $obj = new \stdClass();
        $obj->draw            = intval($request->get('draw'));
        $obj->recordsTotal    = $countAll;
        $obj->recordsFiltered = $countFiltered;
        $obj->data            = $letters;
        if ($error != '') {
            $obj->error = trim($error, "\r\n");
        }

        return new JsonResponse($obj);
    }

    private function generateDataTablesError($message)
    {
        $obj = new \stdClass();
        $obj->error = $message;
        return new JsonResponse($obj);

    }
}
