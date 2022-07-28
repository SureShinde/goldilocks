<?php
namespace Magenest\FbChatbot\Model\Config\Backend;

class SetupHumanSupport extends AbstractSetup {
    public function beforeSave()
    {
        $isHumanSupport = $this->getData('fieldset_data/enable');
        $humanSupportMenu = $this->menuRepository->getById(1);
        if ($humanSupportMenu->getData('is_active') != $isHumanSupport){
            $humanSupportMenu->setData('is_active',$isHumanSupport);
            $this->menuRepository->save($humanSupportMenu);
            $this->bot->setupPersistentMenu();
        }
        parent::beforeSave();
    }
}
