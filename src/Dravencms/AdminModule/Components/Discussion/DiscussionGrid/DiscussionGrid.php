<?php

/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\Discussion\DiscussionGrid;

use Dravencms\Components\BaseGridFactory;
use Dravencms\Model\Discussion\Repository\DiscussionRepository;
use App\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

/**
 * Description of DiscussionGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class DiscussionGrid extends Control
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var DiscussionRepository */
    private $discussionRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var LocaleRepository */
    private $localeRepository;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * DiscussionGrid constructor.
     * @param DiscussionRepository $discussionRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param LocaleRepository $localeRepository
     */
    public function __construct(DiscussionRepository $discussionRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager, LocaleRepository $localeRepository)
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->discussionRepository = $discussionRepository;
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeRepository;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->discussionRepository->getDiscussionQueryBuilder());

        $grid->addColumnText('name', 'Name')
            ->setFilterText()
            ->setSuggestion();

        $grid->addColumnDate('updatedAt', 'Last edit', $this->localeRepository->getLocalizedDateTimeFormat())
            ->setSortable()
            ->setFilterDate();
        $grid->getColumn('updatedAt')->cellPrototype->class[] = 'center';

        $grid->addColumnBoolean('isActive', 'Active');


        if ($this->presenter->isAllowed('discussion', 'edit')) {

            $grid->addActionHref('posts', 'Posts')
                ->setIcon('list');

            $grid->addActionHref('edit', 'Upravit')
                ->setIcon('pencil');
        }

        if ($this->presenter->isAllowed('discussion', 'delete')) {
            $grid->addActionHref('delete', 'Smazat', 'delete!')
                ->setCustomHref(function($row){
                    return $this->link('delete!', $row->getId());
                })
                ->setIcon('trash-o')
                ->setConfirm(function ($row) {
                    return ['Opravdu chcete smazat diskuzi %s ?', $row->getName()];
                });


            $operations = ['delete' => 'Smazat'];
            $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
                ->setConfirm('delete', 'Opravu chcete smazat %i discussion ?');
        }
        $grid->setExport();

        return $grid;
    }

    /**
     * @param $action
     * @param $ids
     */
    public function gridOperationsHandler($action, $ids)
    {
        switch ($action)
        {
            case 'delete':
                $this->handleDelete($ids);
                break;
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        $discussions = $this->discussionRepository->getById($id);
        foreach ($discussions AS $discussion)
        {
            $this->entityManager->remove($discussion);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/DiscussionGrid.latte');
        $template->render();
    }
}
