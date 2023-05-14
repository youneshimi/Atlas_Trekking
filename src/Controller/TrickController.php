<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Contributor;
use App\Entity\Media;
use App\Entity\Trick;
use App\Form\CommentFormType;
use App\Form\TrickType;
use App\Services\FileUploader;
use App\Services\Slug;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class TrickController extends AbstractController
{
    /**
     * @Route("/trick/editer/{slug}/", name="edit_trick")
     * @param Trick           $trick
     * @param ManagerRegistry $doctrine
     * @param Request         $request
     * @param FileUploader    $fileUploader
     * @return RedirectResponse|Response
     */
    public function editTrick(Trick $trick, ManagerRegistry $doctrine, Request $request, FileUploader $fileUploader): RedirectResponse|Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $editTrickForm = $this->createForm(TrickType::class, $trick);
        $editTrickForm->handleRequest($request);

        if ($editTrickForm->isSubmitted() && $editTrickForm->isValid()) {


            $entityManager = $doctrine->getManager();
            $trick = $editTrickForm->getData();

            $trick->setUser($this->getUser());

            $medias = $editTrickForm->get('medias');

            foreach ($medias as $media) {
                $newMedia = $media->getData();

                if ($newMedia->getType()==Media::TYPE_IMAGE) {
                    if ($media->get('image')->getData()!==null) {

                        if (sizeof($trick->getMedias())>0 && is_null($trick->getCoverImg())) {
                            $trick->setCoverImg($newMedia);
                        }

                        $fileName = $fileUploader->upload($media->get('image')->getData());
                        $newMedia->setLink($fileName);

                    }
                }
                if ($newMedia->getType()==Media::TYPE_VIDEO) {
                    $newMedia->setAlt('Intégration vidéo externe');
                }
                $media->getData()->setTrick($trick);
                $trick->addMedia($media->getData());
            }
            $encoders = [new XmlEncoder(), new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);

            $mediasToArray = $serializer->normalize($trick->getMedias(), null, [
                AbstractNormalizer::ATTRIBUTES => ['id', 'type', 'link', 'alt']
            ]);

            if (!array_search($trick->getCoverImg()->getLink(), $mediasToArray)) {
                $trick->setCoverImg(null);
            }

            $contributor = new Contributor();
            $contributor->setTrick($trick);
            $contributor->setUser($this->getUser());
            $trick->addContributor($contributor);

            $entityManager->persist($trick);
            $entityManager->flush();

            $this->addFlash('success', 'Activité '.$trick->getName().' a bien été modifié !');
            return $this->redirectToRoute('show_index');
        }

        return $this->renderForm('@client/pages/editTrick.html.twig', [
            'addTrickForm' => $editTrickForm
        ]);
    }

    /**
     * @Route("/trick/ajouter/", name="add_trick")
     * @param Slug            $slug
     * @param ManagerRegistry $doctrine
     * @param Request         $request
     * @param FileUploader    $fileUploader
     * @return RedirectResponse|Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function createTrick(Slug $slug, ManagerRegistry $doctrine, Request $request, FileUploader $fileUploader): RedirectResponse|Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $trick = new Trick();
        $trickForm = $this->createForm(TrickType::class, $trick);
        $trickForm->handleRequest($request);

        if ($trickForm->isSubmitted() && $trickForm->isValid()) {
                $entityManager = $doctrine->getManager();
                $trick = $trickForm->getData();

                $trick->setUser($this->getUser());
                $trickRepository = $doctrine->getRepository(Trick::class);

                $slug = $slug->generate($trick->getName());
                $trick->setSlug($trickRepository->adaptToExistingSlug($slug));

                $medias = $trickForm->get('medias');
                $cover = false;

                foreach ($medias as $media) {
                    $newMedia = $media->getData();
                    if ($newMedia->getType()==Media::TYPE_IMAGE) {

                        $fileName = $fileUploader->upload($media->get('image')->getData());
                        $newMedia->setLink($fileName);

                        if(!$cover){
                            $trick->setCoverImg($newMedia);
                            $cover = true;
                        }

                    }
                    if ($newMedia->getType()==Media::TYPE_VIDEO) {
                        $newMedia->setAlt('Intégration vidéo externe');
                    }

                    $media->getData()->setTrick($trick);
                    $trick->addMedia($media->getData());
                }

                $entityManager->persist($trick);
                $entityManager->flush();

                $this->addFlash('success', 'Le trick '.$trick->getName().' a bien été créé !');
                return $this->redirectToRoute('show_index');
        }

        return $this->renderForm('@client/pages/addTrick.html.twig', [
            'addTrickForm' => $trickForm
        ]);
    }


    /**
     * @Route("/trick/{slug}", name="show_trick")
     * @param Trick           $trick
     * @param ManagerRegistry $doctrine
     * @param Request         $request
     * @return RedirectResponse|Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function showTrick(Trick $trick, ManagerRegistry $doctrine, Request $request): RedirectResponse|Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setStatus(false);
            $comment->setUser($this->getUser());
            $comment->setTrick($trick);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été pris en compte, il sera traité et mis en ligne dans les plus brefs délais');
            return $this->redirectToRoute('show_trick', ['slug' => $trick->getSlug()]);
        }

        $comments = $doctrine
            ->getRepository(Comment::class)
            ->findBy(
                [
                    'trick' => $trick->getId(),
                    'status' => true
                ],
                [
                    'createdAt' => 'DESC'
                ],
                10
            );

        $count = $doctrine
            ->getRepository(Comment::class)
            ->count($trick->getId());

        return $this->render('@client/pages/trick.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'remain_comments' => ($count > 10),
            'commentForm' => $form->createView()
        ]);
    }


    /**
     * @Route("/trick/couverture/{slug}/{image}/", name="setcover_trick")
     * @param Trick           $trick
     * @param Media           $image
     * @param ManagerRegistry $doctrine
     * @return RedirectResponse
     */
    public function changeCover(Trick $trick, Media $image, ManagerRegistry $doctrine): RedirectResponse
    {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            $contributor = new Contributor();
            $contributor->setTrick($trick);
            $contributor->setUser($this->getUser());
            $trick->addContributor($contributor);

            $trick->setCoverImg($image);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($trick);
            $entityManager->flush();


            $this->addFlash('success', 'L\'image de couverture a bien été mise à jour');
            return $this->redirectToRoute('show_trick', ['slug' => $trick->getSlug()]);
    }


    /**
     * @Route("/trick/supprimer/{slug}/", name="delete_trick")
     * @param Trick           $trick
     * @param ManagerRegistry $doctrine
     * @return RedirectResponse|void
     */
    public function deleteTrick(Trick $trick, ManagerRegistry $doctrine) {
        if ($this->isGranted('delete', $trick)) {
            $entityManager = $doctrine->getManager();

            $entityManager->remove($trick);
            $entityManager->flush();

            $this->addFlash('success', 'Activité'.$trick->getName().' a bien été supprimé');
            return $this->redirectToRoute('show_index');
        }
    }

}
