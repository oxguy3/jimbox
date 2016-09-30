<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Letter;
use AppBundle\Form\LetterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:default:index.html.twig', array());
    }

    /**
     * @Route("/letters/new", name="letters_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function lettersNewAction(Request $request)
    {
        $letter = new Letter();

        $form = $this->createForm(LetterType::class, $letter);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $letter = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($letter);
            $em->flush();

            return $this->redirectToRoute('letter_view', ['id' => $letter->getId()]);
        }

        return $this->render('AppBundle:default:letters_new.html.twig', array(
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
        return $this->render('AppBundle:default:letters_list.html.twig');
    }

    /**
     * @Route("/letter/{id}", name="letter_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function letterViewAction(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Letter');
        $qb = $repo->createQueryBuilder('l')
            ->where('l.id = :id')
            ->setParameter('id', $id);
        $letter = $qb->getQuery()->getOneOrNullResult();

        return $this->render('AppBundle:default:letter_view.html.twig', [
            'letter' => $letter->jsonSerialize(),
        ]);
    }

    /**
     * @Route("/api/letters", name="api_letters")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function lettersListJsonAction(Request $request)
    {
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

            // if a column is marked unsearchable and unorderable, skip it
            if ($value['searchable'] == 'false' && $value['orderable'] == 'false') {
                continue;
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
            $column  = $columns[intval($o['column'])];
            if ($column['orderable'] == 'false') {
                return $this->generateDataTablesError("Tried to order an non-orderable column.");
            }
            $colName = 'l.'.$column['data'];
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
