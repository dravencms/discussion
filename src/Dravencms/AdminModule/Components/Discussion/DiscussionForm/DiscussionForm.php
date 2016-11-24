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

namespace Dravencms\AdminModule\Components\Discussion;

use Dravencms\Components\BaseFormFactory;
use App\Model\Discussion\Entities\Discussion;
use App\Model\Discussion\Repository\DiscussionRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of DiscussionForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class DiscussionForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var DiscussionRepository */
    private $faqRepository;

    /** @var Discussion|null */
    private $discussion = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * DiscussionForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param DiscussionRepository $faqRepository
     * @param Discussion|null $discussion
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        DiscussionRepository $faqRepository,
        Discussion $discussion = null
    ) {
        parent::__construct();

        $this->discussion = $discussion;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->faqRepository = $faqRepository;


        if ($this->discussion) {
            $defaults = [
                'name' => $this->discussion->getName(),
                'isShowName' => $this->discussion->isShowName(),
                'isActive' => $this->discussion->isActive(),
            ];

        }
        else{
            $defaults = [
                'isActive' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    /**
     * @return \Dravencms\Components\BaseForm
     */
    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        $form->addText('name')
            ->setRequired('Please enter name.');
        

        $form->addCheckbox('isActive');
        $form->addCheckbox('isShowName');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();
        if (!$this->faqRepository->isNameFree($values->name, $this->discussion)) {
            $form->addError('Tento name je již zabrán.');
        }

        if (!$this->presenter->isAllowed('discussion', 'edit')) {
            $form->addError('Nemáte oprávění editovat discussion.');
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();
        
        if ($this->discussion) {
            $discussion = $this->discussion;
            $discussion->setName($values->name);
            $discussion->setIsShowName($values->isShowName);
            $discussion->setIsActive($values->isActive);
        } else {
            $discussion = new Discussion($values->name, $values->isActive, $values->isShowName);
        }


        $this->entityManager->persist($discussion);

        $this->entityManager->flush();

        $this->onSuccess();
    }


    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/DiscussionForm.latte');
        $template->render();
    }
}