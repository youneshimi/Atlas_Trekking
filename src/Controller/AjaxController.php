<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Security\Core\Security;

class AjaxController extends AbstractController
{
    /**
     * @Route("/change-comment-status/{$comment_id}/", name="_change_commentStatus_ajax")
     * @param ManagerRegistry $doctrine
     * @param int             $comment_id
     * @return JsonResponse
     * @throws Exception
     */
    public function changeCommentStatus(ManagerRegistry $doctrine, int $comment_id): JsonResponse
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');


        $repository = $doctrine->getRepository(Comment::class);
        $repository->toggleCommentStatus($comment_id);

        return $this->json([
            'success' => true,
            'comment' => $comment_id
        ], 200, [], ['groups' => ['trick', 'user', 'comment', 'datetime']]);
    }


    /**
     * @param ManagerRegistry $doctrine
     * @param int             $id
     * @param int             $limit
     * @param int             $offset
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws ExceptionInterface
     */
    public function loadMoreComments(ManagerRegistry $doctrine, int $id, int $limit = 10, int $offset = 10): JsonResponse
    {
        $elements = $doctrine
            ->getRepository(Comment::class)
            ->findNextComments(
                $id,
                $limit,
                $offset
            );

        $count = $doctrine
            ->getRepository(Comment::class)
            ->count($id);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->normalize($elements, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'content', 'status', 'createdAt', 'user' => ['username', 'image']]
        ]);

        $jsonContent = $serializer->serialize(
            [
                'success' => true,
                'data' => $jsonContent,
                'remain' => ($count > ($limit + $offset))
            ],
            'json'
        );

        return new JsonResponse($jsonContent, 200, [], true);
    }


    /**
     * @Route("/load-tricks/{id}/{limit}/{offset}", name="_loadMore_tricks_ajax")
     * @param ManagerRegistry $doctrine
     * @param Security        $security
     * @param int             $limit
     * @param int             $offset
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function loadMoreTricks(ManagerRegistry $doctrine, Security $security, int $limit = 8, int $offset = 8): JsonResponse
    {
        $elements = $doctrine
            ->getRepository(Trick::class)
            ->findBy(
                [],
                ['createdAt' => 'DESC'],
                $limit,
                $offset
            );

        $tricks = [];
        foreach ($elements as $trick) {
            $tricks[] = [
                'element' => $trick,
                'permissions' => [
                    'canEdit' => $security->isGranted('IS_AUTHENTICATED_FULLY'),
                    'canDelete' => $security->isGranted('delete', $trick)
                ]
            ];
        }

        $count = $doctrine
            ->getRepository(Trick::class)
            ->size();

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->normalize($tricks, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'name', 'description', 'slug', 'coverImg' => ['type', 'link', 'alt'], 'user' => ['username']]
        ]);

        $jsonContent = $serializer->serialize(
            [
                'success' => true,
                'data' => $jsonContent,
                'remain' => ($count > ($limit + $offset))
            ],
            'json'
        );

        return new JsonResponse($jsonContent, 200, [], true);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param int             $asked
     * @return bool
     */
    private function checkTricksRemain(ManagerRegistry $doctrine, int $asked): bool
    {
        return count($doctrine->getRepository(Trick::class)->findAll()) >= $asked;
    }


    /**
     * @param ManagerRegistry $doctrine
     * @param string          $slug
     * @param int             $limit
     * @param int             $offset
     * @return bool
     */
    private function checkCommentsRemain(ManagerRegistry $doctrine, string $slug, int $limit, int $offset): bool
    {
        $remains = $doctrine
            ->getRepository(Comment::class)
            ->findNextComments(
                $slug,
                $limit,
                $offset + $limit
            );

        return !empty($remains);
    }


}
