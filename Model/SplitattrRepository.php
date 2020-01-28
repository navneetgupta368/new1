<?php
/**
 * Sunarc_Splitorderpro extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the SunArc Technologies License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://sunarctechnologies.com/end-user-agreement/
 *
 * @category  Sunarc
 * @package   Sunarc_Splitorderpro
 * @copyright Copyright (c) 2017
 * @license
 */
namespace Sunarc\Splitorderpro\Model;

class SplitattrRepository implements \Sunarc\Splitorderpro\Api\SplitattrRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Splitattr resource model
     *
     * @var \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr
     */
    protected $resource;

    /**
     * Splitattr collection factory
     *
     * @var \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr\CollectionFactory
     */
    protected $splitattrCollectionFactory;

    /**
     * Splitattr interface factory
     *
     * @var \Sunarc\Splitorderpro\Api\Data\SplitattrInterfaceFactory
     */
    protected $splitattrInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var \Sunarc\Splitorderpro\Api\Data\SplitattrSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     *
     * @param \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr $resource
     * @param \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr\CollectionFactory $splitattrCollectionFactory
     * @param \Sunarc\Splitorderpro\Api\Data\SplitattrInterfaceFactory $splitattrInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Sunarc\Splitorderpro\Api\Data\SplitattrSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr $resource,
        \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr\CollectionFactory $splitattrCollectionFactory,
        \Sunarc\Splitorderpro\Api\Data\SplitattrInterfaceFactory $splitattrInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Sunarc\Splitorderpro\Api\Data\SplitattrSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resource                   = $resource;
        $this->splitattrCollectionFactory = $splitattrCollectionFactory;
        $this->splitattrInterfaceFactory  = $splitattrInterfaceFactory;
        $this->dataObjectHelper           = $dataObjectHelper;
        $this->searchResultsFactory       = $searchResultsFactory;
    }

    /**
     * Save Splitattr.
     *
     * @param \Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr
     * @return \Sunarc\Splitorderpro\Api\Data\SplitattrInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr)
    {
        /** @var \Sunarc\Splitorderpro\Api\Data\SplitattrInterface|\Magento\Framework\Model\AbstractModel $splitattr */
        try {
            $this->resource->save($splitattr);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Splitattr: %1',
                $exception->getMessage()
            ));
        }
        return $splitattr;
    }

    /**
     * Retrieve Splitattr.
     *
     * @param int $splitattrId
     * @return \Sunarc\Splitorderpro\Api\Data\SplitattrInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($splitattrId)
    {
        if (!isset($this->instances[$splitattrId])) {
            /** @var \Sunarc\Splitorderpro\Api\Data\SplitattrInterface|\Magento\Framework\Model\AbstractModel $splitattr */
            $splitattr = $this->splitattrInterfaceFactory->create();
            $this->resource->load($splitattr, $splitattrId);
            if (!$splitattr->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested Splitattr doesn\'t exist'));
            }
            $this->instances[$splitattrId] = $splitattr;
        }
        return $this->instances[$splitattrId];
    }

    /**
     * Retrieve Splitattrs matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Sunarc\Splitorderpro\Api\Data\SplitattrSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Sunarc\Splitorderpro\Api\Data\SplitattrSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr\Collection $collection */
        $collection = $this->splitattrCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == \Magento\Framework\Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            // set a default sorting order since this method is used constantly in many
            // different blocks
            $field = 'splitattr_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Sunarc\Splitorderpro\Api\Data\SplitattrInterface[] $splitattrs */
        $splitattrs = [];
        /** @var \Sunarc\Splitorderpro\Model\Splitattr $splitattr */
        foreach ($collection as $splitattr) {
            /** @var \Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattrDataObject */
            $splitattrDataObject = $this->splitattrInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $splitattrDataObject,
                $splitattr->getData(),
                \Sunarc\Splitorderpro\Api\Data\SplitattrInterface::class
            );
            $splitattrs[] = $splitattrDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($splitattrs);
    }

    /**
     * Delete Splitattr.
     *
     * @param \Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Sunarc\Splitorderpro\Api\Data\SplitattrInterface $splitattr)
    {
        /** @var \Sunarc\Splitorderpro\Api\Data\SplitattrInterface|\Magento\Framework\Model\AbstractModel $splitattr */
        $id = $splitattr->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($splitattr);
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Splitattr %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Splitattr by ID.
     *
     * @param int $splitattrId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($splitattrId)
    {
        $splitattr = $this->getById($splitattrId);
        return $this->delete($splitattr);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Sunarc\Splitorderpro\Model\ResourceModel\Splitattr\Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }
}
