<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    /**
     * @Route("/admin", name="show_admin")
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    public function showAdmin(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tricks = $doctrine
            ->getRepository(Trick::class)
            ->findBy(
                [],
                ['createdAt' => 'DESC']
            );

        return $this->render('@client/pages/admin.html.twig', [
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/admin/commentaire/{comment_id}/changer-statut/", name="change_commentStatus")
     * @param ManagerRegistry $doctrine
     * @param int             $comment_id
     * @return RedirectResponse
     * @throws Exception
     */
    public function changeCommentStatus(ManagerRegistry $doctrine, int $comment_id): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $doctrine->getRepository(Comment::class);
        $repository->toggleCommentStatus($comment_id);

        $this->addFlash('success', 'Le statut du commentaire a bien été mis à jour !');
        return $this->redirectToRoute('show_admin');
    }


}
