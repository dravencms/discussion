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
class DiscussionCmsRepository implements ICmsComponentRepository
{
    /** @var DiscussionRepository  */
    private $discussionRepository;

    /**
     * DiscussionCmsRepository constructor.
     * @param DiscussionRepository $discussionRepository
     */
    public function __construct(DiscussionRepository $discussionRepository)
    {
        $this->discussionRepository = $discussionRepository;
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
                foreach ($this->discussionRepository->getActive() AS $discussion) {
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
        $found = $this->discussionRepository->findTranslatedOneBy($this->discussionRepository, $locale, $parameters + ['isActive' => true]);

        if ($found)
        {
            return new CmsActionOption( $found->getName(), $parameters);
        }

        return null;
    }

}