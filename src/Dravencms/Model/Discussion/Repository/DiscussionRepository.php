<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Discussion\Repository;

use Dravencms\Locale\TLocalizedRepository;
use Dravencms\Model\Discussion\Entities\Discussion;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

/**
 * Class DiscussionRepository
 * @package App\Model\Carousel\Repository
 */
class DiscussionRepository implements ICmsComponentRepository
{
    use TLocalizedRepository;
    
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $discussionRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * DiscussionRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->discussionRepository = $entityManager->getRepository(Discussion::class);
    }

    /**
     * @param $id
     * @return mixed|null|Discussion
     */
    public function getOneById($id)
    {
        return $this->discussionRepository->find($id);
    }

    /**
     * @param $id
     * @return Discussion[]
     */
    public function getById($id)
    {
        return $this->discussionRepository->findBy(['id' => $id]);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDiscussionQueryBuilder()
    {
        $qb = $this->discussionRepository->createQueryBuilder('d')
            ->select('d');
        return $qb;
    }

    /**
     * @return Discussion[]
     */
    public function getActive()
    {
        return $this->discussionRepository->findBy(['isActive' => true]);
    }

    /**
     * @param $name
     * @param Discussion|null $discussionIgnore
     * @return boolean
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, Discussion $discussionIgnore = null)
    {
        $qb = $this->discussionRepository->createQueryBuilder('d')
            ->select('d')
            ->where('d.name = :name')
            ->setParameters([
                'name' => $name,
            ]);

        if ($discussionIgnore)
        {
            $qb->andWhere('d != :discussionIgnore')
                ->setParameter('discussionIgnore', $discussionIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
            case 'OverviewDetail':
                $return = [];
                /** @var Discussion $discussion */
                foreach ($this->discussionRepository->findBy(['isActive' => true]) AS $discussion) {
                    $return[] = new CmsActionOption($discussion->getName(), ['id' => $discussion->getId()]);
                }
                break;

            case 'Overview':
            case 'SimpleOverview':
            case 'Navigation':
                return null;
                break;

            default:
                return false;
                break;
        }


        return $return;
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @param ILocale $locale
     * @return null|CmsActionOption
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale)
    {
        /** @var Discussion $found */
        $found = $this->findTranslatedOneBy($this->discussionRepository, $locale, $parameters + ['isActive' => true]);

        if ($found)
        {
            return new CmsActionOption( $found->getName(), $parameters);
        }

        return null;
    }

}