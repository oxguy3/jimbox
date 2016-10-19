<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Document;
use AppBundle\Entity\Letter;
use AppBundle\Form\LetterType;
use AppBundle\Form\DocumentType;
use Gaufrette\StreamWrapper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * @Route("/letter/{id}/docs/upload", name="document_upload")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function documentUploadAction(Request $request, $id)
    {
        // get the Letter in question
        $repo = $this->getDoctrine()->getRepository('AppBundle:Letter');
        $qb = $repo->createQueryBuilder('l')
            ->where('l.id = :id')
            ->setParameter('id', $id);
        $letter = $qb->getQuery()->getOneOrNullResult();

        if ($letter == null) {
            throw $this->createNotFoundException('Letter #'.$id.' does not exist.');
        }

        // let's validate the document form
        $document = new Document();

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->getData();
            $document->setLetter($letter);
            $file = $document->getFile();

            $filesystem = $this->container->get('knp_gaufrette.filesystem_map')->get('letters');
            $adapter    = $filesystem->getAdapter();
            $adapter->setMetadata($id.'/'.$document->getName(), array('contentType' => $file->getClientMimeType()));
            $adapter->write($id.'/'.$document->getName(), file_get_contents($file->getPathname()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($document);
            $em->flush();

            return $this->redirectToRoute('letter_view', ['id' => $letter->getId()]);
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * @Route("/letter/{id}/docs/{file}", name="document_read")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function documentReadAction(Request $request, $id, $file)
    {
        $key      = $id.'/'.$file;
        $s3bucket = $this->container->getParameter('aws_s3_bucket');

        $s3client = $this->container->get('acme.aws_s3.client');
        $request  = $s3client->get($s3bucket.'/'.$key);
        $url      = $s3client->createPresignedUrl($request, '+1 hour');

        return new RedirectResponse($url, 302);
    }

//    /**
//     * @Route("/letter/{id}/docs/delete/{docid}", name="document_read")
//     * @param Request $request
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function documentDeleteAction(Request $request, $id, $docid)
//    {
//        $filesystem = $this->container->get('knp_gaufrette.filesystem_map')->get('letters');
//        $map = StreamWrapper::getFilesystemMap();
//        $map->set('letters', $filesystem);
//        $filepath = 'gaufrette://letters/'.$id.'/'.$file;
//
//        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($filepath);
//        return $response;
//    }

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
        $repoLtrs = $this->getDoctrine()->getRepository('AppBundle:Letter');
        $qbLtrs   = $repoLtrs->createQueryBuilder('l')
            ->where('l.id = :id')
            ->setParameter('id', $id);
        $letter = $qbLtrs->getQuery()->getOneOrNullResult();

        if (is_null($letter)) {
            throw $this->createNotFoundException('The letter does not exist.');
        }

        $repoDocs = $this->getDoctrine()->getRepository('AppBundle:Document');
        $qbDocs   = $repoDocs->createQueryBuilder('d')
            ->where('d.letter = :l')
            ->setParameter('l', $letter);
        $docs = $qbDocs->getQuery()->getResult();

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);

        return $this->render('AppBundle:default:letter_view.html.twig', [
            'letter' => $letter->jsonSerialize(),
            'documents' => $docs,
            'documentForm' => $form->createView()
        ]);
    }


    /**
     * @Route("/letter/{id}/edit", name="letter_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function letterEditAction(Request $request, $id)
    {
        $letter = $this->getDoctrine()->getRepository('AppBundle:Letter')->find($id);

        if (is_null($letter)) {
            throw $this->createNotFoundException('The letter does not exist.');
        }

        $form = $this->createForm(LetterType::class, $letter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $letter = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('letter_view', ['id' => $letter->getId()]);
        }

        return $this->render('AppBundle:default:letter_edit.html.twig', array(
            'letter'     => $letter->jsonSerialize(),
            'letterForm' => $form->createView(),
        ));
    }

    /**
     * @Route("/letters/json", name="letters_json")
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
