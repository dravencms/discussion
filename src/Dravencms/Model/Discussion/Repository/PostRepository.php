<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Discussion\Repository;

use Dravencms\Model\Discussion\Entities\Discussion;
use Dravencms\Model\Discussion\Entities\Post;
use Kdyby\Doctrine\EntityManager;
use Nette;

/**
 * Class PostRepository
 * @package App\Model\Carousel\Repository
 */
class PostRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $postRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * PostRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->postRepository = $entityManager->getRepository(Post::class);
    }

    /**
     * @param $id
     * @return mixed|null|Post
     */
    public function getOneById($id)
    {
        return $this->postRepository->find($id);
    }

    /**
     * @param $id
     * @return Post[]
     */
    public function getById($id)
    {
        return $this->postRepository->findBy(['id' => $id]);
    }

    /**
     * @param Discussion $discussion
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPostQueryBuilder(Discussion $discussion)
    {
        $qb = $this->postRepository->createQueryBuilder('p')
            ->select('p')
            ->where('p.discussion = :discussion')
            ->setParameter('discussion', $discussion);
        return $qb;
    }
}