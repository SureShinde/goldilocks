<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\Grid;

use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Model\BookmarkFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Authorization\Model\UserContextInterface;

class BookmarkManagement
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var BookmarkRepositoryInterface
     */
    private $bookmarkRepository;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var BookmarkInterface
     */
    private $bookmark;

    /**
     * @var BookmarkFactory
     */
    private $bookmarkFactory;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    public function __construct(
        BookmarkManagementInterface $bookmarkManagement,
        BookmarkRepositoryInterface $bookmarkRepository,
        EncoderInterface $encoder,
        UserContextInterface $userContext,
        BookmarkFactory $bookmarkFactory
    ) {
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->encoder = $encoder;
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
    }

    /**
     * @param string $namespace
     * @return BookmarkManagement
     */
    public function load(string $namespace): BookmarkManagement
    {
        $this->bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            BookmarkInterface::CURRENT,
            $namespace
        );
        if (!$this->bookmark) {
            $this->bookmark = $this->bookmarkFactory->create()
                ->setNamespace($namespace)
                ->setIdentifier(BookmarkInterface::CURRENT)
                ->setConfig([])
                ->setUserId($this->userContext->getUserId());
        }

        return $this;
    }

    public function clear(): void
    {
        $this->bookmark = null;
    }

    /**
     * $filters array like [code => value]
     *
     * @param string $namespace
     * @param array $filters
     */
    public function applyFilter(string $namespace, array $filters): void
    {
        if (!$this->bookmark) {
            $this->load($namespace);
        }

        $config = $this->bookmark->getConfig();
        // clear previous filters
        $config[BookmarkInterface::CURRENT]['filters'] = ['applied' => []];

        foreach ($filters as $filterCode => $filterValue) {
            $this->injectFilter($config, $filterCode, $filterValue);
        }
        $this->bookmark->setConfig($this->encoder->encode($config));

        $this->bookmarkRepository->save($this->bookmark);
    }

    /**
     * Modify config array with passed filter.
     *
     * @param array $config
     * @param string $filterCode
     * @param mixed $filterValue
     * @return void
     */
    private function injectFilter(array &$config, string $filterCode, $filterValue): void
    {
        if ($filterValue !== null && isset($config[BookmarkInterface::CURRENT]['filters']['applied'])) {
            $config[BookmarkInterface::CURRENT]['filters']['applied'][$filterCode] = $filterValue;
        }
    }
}
