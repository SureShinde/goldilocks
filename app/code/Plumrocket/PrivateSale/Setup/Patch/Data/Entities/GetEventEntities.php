<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license/  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Data\Entities;

use Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Model\Attribute\Backend\Image;
use Plumrocket\PrivateSale\Model\Event;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\Attribute\Collection as EventAttributeCollection;

/**
 * Get event entities data
 *
 * @since 5.1.0
 */
class GetEventEntities
{
    /**
     * Get checkbox entity
     *
     * @return array[]
     */
    public function execute(): array
    {
        $eventEntity = Event::ENTITY;

        return [
            $eventEntity => [
                'attribute_model' => \Plumrocket\PrivateSale\Model\Event\Attribute::class,
                'entity_model' => \Plumrocket\PrivateSale\Model\ResourceModel\Event::class,
                'table' => $eventEntity . '_entity',
                'additional_attribute_table' => $eventEntity . '_eav_attribute',
                'entity_attribute_collection' => EventAttributeCollection::class,
                'attributes' => [
                    EventInterface::EVENT_NAME => [
                        'type' => 'text',
                        'label' => 'Event Name',
                        'required' => true,
                        'input' => 'text',
                        'sort_order' => 20,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Event',
                    ],
                    EventInterface::EVENT_TYPE => [
                        'type' => 'static',
                        'label' => 'Event Type',
                        'required' => false,
                        'default' => 1,
                        'input' => 'select',
                        'source' => \Plumrocket\PrivateSale\Model\Config\Source\EventType::class,
                        'sort_order' => 30,
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'group' => 'Event',
                    ],
                    EventInterface::IS_ENABLED => [
                        'type' => 'int',
                        'label' => 'Enable Event',
                        'required' => false,
                        'default' => 1,
                        'input' => 'boolean',
                        'source' => Boolean::class,
                        'sort_order' => 10,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'Event',
                    ],
                    EventInterface::CATEGORY_EVENT => [
                        'type' => 'int',
                        'label' => 'Event Category',
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'required' => false,
                        'sort_order' => 40,
                        'visible' => true,
                        'group' => 'Event'
                    ],
                    EventInterface::PRODUCT_EVENT => [
                        'type' => 'int',
                        'label' => 'Event Product',
                        'required' => false,
                        'input' => 'text',
                        'sort_order' => 50,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'Event'
                    ],
                    EventInterface::EVENT_FROM => [
                        'type' => 'datetime',
                        'label' => 'Event From',
                        'required' => true,
                        'input' => 'date',
                        'sort_order' => 60,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'Event'
                    ],
                    EventInterface::EVENT_TO => [
                        'type' => 'datetime',
                        'label' => 'Event To',
                        'required' => true,
                        'input' => 'date',
                        'sort_order' => 70,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'Event'
                    ],
                    EventInterface::EVENT_IMAGE => [
                        'type' => 'varchar',
                        'label' => 'Event Thumbnail Image',
                        'input' => 'image',
                        'backend' => Image::class,
                        'required' => false,
                        'sort_order' => 10,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Content',
                    ],
                    'small_image' => [
                        'type' => 'varchar',
                        'label' => 'Event Thumbnail Mobile Image',
                        'input' => 'image',
                        'backend' => Image::class,
                        'required' => false,
                        'sort_order' => 20,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Content',
                        'note' => 'If a mobile thumbnail is not uploaded, then regular event thumbnail will be
                            displayed on mobile devices.'
                    ],
                    EventInterface::EVENT_VIDEO => [
                        'type' => 'text',
                        'label' => 'Event Video',
                        'required' => false,
                        'input' => 'text',
                        'sort_order' => 30,
                        'backend' => JsonEncoded::class,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Content',
                    ],
                    'newsletter_image' => [
                        'type' => 'varchar',
                        'label' => 'Newsletter image',
                        'input' => 'image',
                        'backend' => Image::class,
                        'required' => false,
                        'sort_order' => 40,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Content',
                        'note' => 'Email image is used in flash sale newsletter templates.'
                    ],
                    EventInterface::HEADER_IMAGE => [
                        'type' => 'varchar',
                        'label' => 'Event Header Image',
                        'input' => 'image',
                        'backend' => Image::class,
                        'required' => false,
                        'sort_order' => 50,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Content',
                        'note' => 'If a header image is not uploaded, then regular event thumbnail will be
                            used for event header.'
                    ],
                    EventInterface::PRIORITY => [
                        'type' => 'int',
                        'label' => 'Priority',
                        'required' => false,
                        'input' => 'text',
                        'sort_order' => 100,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'Event'
                    ],
                    'event_permissions' => [
                        'type' => 'int',
                        'label' => 'Use Default Config Settings',
                        'required' => false,
                        'input' => 'select',
                        'source' => Boolean::class,
                        'sort_order' => 10,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'General Permissions'
                    ],
                    'before_event_starts' => [
                        'type' => 'text',
                        'label' => 'Before Event Starts',
                        'required' => false,
                        'input' => 'text',
                        'backend' => JsonEncoded::class,
                        'sort_order' => 20,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'General Permissions'
                    ],
                    'after_event_ends' => [
                        'type' => 'text',
                        'label' => 'After Event Ends',
                        'required' => false,
                        'input' => 'text',
                        'backend' => JsonEncoded::class,
                        'sort_order' => 30,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'General Permissions'
                    ],
                    EventInterface::IS_PRIVATE => [
                        'type' => 'int',
                        'label' => 'Enable Event',
                        'required' => false,
                        'default' => 0,
                        'input' => 'boolean',
                        'source' => Boolean::class,
                        'sort_order' => 10,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'Private Sale Permissions',
                        'note' => 'Enable restricted, members-only access for the duration of the event.'
                    ],
                    'private_event_permissions' => [
                        'type' => 'int',
                        'label' => 'Use Default Config Settings',
                        'required' => false,
                        'input' => 'select',
                        'source' => Boolean::class,
                        'sort_order' => 20,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'Private Sale Permissions'
                    ],
                    'custom_permissions' => [
                        'type' => 'text',
                        'label' => 'Custom Permissions',
                        'required' => false,
                        'input' => 'text',
                        'backend' => \Plumrocket\PrivateSale\Model\Attribute\Backend\CustomPermissions::class,
                        'sort_order' => 30,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'Private Sale Permissions'
                    ],
                    'event_landing_page' => [
                        'type' => 'text',
                        'label' => 'Private Event Landing Page',
                        'input' => 'select',
                        'source' => \Plumrocket\PrivateSale\Model\Config\Source\Eventlandingpage::class,
                        'required' => false,
                        'sort_order' => 40,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Private Sale Permissions',
                    ],
                    'enable_banner' => [
                        'type' => 'int',
                        'label' => 'Enable Banner',
                        'required' => false,
                        'default' => 0,
                        'input' => 'boolean',
                        'source' => Boolean::class,
                        'sort_order' => 10,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Promo Banner',
                    ],
                    'banner_position' => [
                        'type' => 'int',
                        'label' => 'Banner position',
                        'input' => 'select',
                        'source' => \Plumrocket\PrivateSale\Model\Config\Source\Bannerposition::class,
                        'required' => false,
                        'sort_order' => 20,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Promo Banner',
                    ],
                    'banner_template' => [
                        'type' => 'text',
                        'label' => 'Banner template',
                        'input' => 'select',
                        'source' => \Plumrocket\PrivateSale\Model\Config\Source\Bannerblocks::class,
                        'required' => false,
                        'sort_order' => 30,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Promo Banner',
                    ],
                    EventInterface::EVENT_DESCRIPTION => [
                        'type' => 'text',
                        'label' => 'Event Description',
                        'input' => 'wysiwyg',
                        'required' => false,
                        'sort_order' => 60,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Content',
                    ],
                ]
            ]
        ];
    }
}
