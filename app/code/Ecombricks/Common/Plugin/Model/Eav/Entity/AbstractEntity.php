<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\Common\Plugin\Model\Eav\Entity;

/**
 * Abstract EAV entity resource plugin
 */
class AbstractEntity extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Before save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function beforeSave(\Magento\Framework\DataObject $object)
    {
        $this->invokeSubjectMethod('_beforeSave', $object);
        return $this;
    }

    /**
     * After save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function afterSave(\Magento\Framework\DataObject $object)
    {
        $this->invokeSubjectMethod('_afterSave', $object);
        return $this;
    }

    /**
     * Save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $subject = $this->getSubject();
        $objectRelationProcessor = $this->getSubjectPropertyValue('objectRelationProcessor');
        if ($object->isDeleted()) {
            return $subject->delete($object);
        }
        if (!$object->hasDataChanges()) {
            return $this;
        }
        $subject->beginTransaction();
        try {
            $object->validateBeforeSave();
            $object->beforeSave();
            if ($object->isSaveAllowed()) {
                if (!$subject->isPartialSave()) {
                    $subject->loadAllAttributes($object);
                }
                if ($subject->getEntityTable() == \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE && !$object->getEntityTypeId()) {
                    $object->setEntityTypeId($subject->getTypeId());
                }
                $object->setParentId((int) $object->getParentId());
                $objectRelationProcessor->validateDataIntegrity($subject->getEntityTable(), $object->getData());
                $this->beforeSave($object);
                $this->invokeSubjectMethod('processSave', $object);
                $this->afterSave($object);
                $object->afterSave();
            }
            $subject->addCommitCallback([$object, 'afterCommitCallback'])->commit();
            $object->setHasDataChanges(false);
        } catch (\Magento\Framework\DB\Adapter\DuplicateException $exception) {
            $subject->rollBack();
            $object->setHasDataChanges(true);
            throw new \Magento\Framework\Exception\AlreadyExistsException(__('Unique constraint violation found'), $exception);
        } catch (\Exception $exception) {
            $subject->rollBack();
            $object->setHasDataChanges(true);
            throw $exception;
        }
        return $this;
    }

    /**
     * Around before save
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $object
     */
    public function aroundBeforeSave(
        \Magento\Eav\Model\Entity\AbstractEntity $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $object
    )
    {
        $this->setSubject($subject);
        $this->beforeSave($object);
    }

    /**
     * Around save
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     */
    public function aroundSave(
        \Magento\Eav\Model\Entity\AbstractEntity $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $object
    )
    {
        $this->setSubject($subject);
        $this->save($object);
        return $subject;
    }

    /**
     * Around after save
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function aroundAfterSave(
        \Magento\Eav\Model\Entity\AbstractEntity $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $object
    )
    {
        $this->setSubject($subject);
        $this->afterSave($object);
    }

}
