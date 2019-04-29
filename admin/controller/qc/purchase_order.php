<?php
require_once(DIR_SYSTEM . 'engine/qccontroller.php');
require_once(DIR_SYSTEM . 'library/quickcommerce/entity_manager.php');
require_once(DIR_SYSTEM . 'library/quickcommerce/lines.php');

use Doctrine\Common\Util\Inflector;
use Doctrine\Common\Util\Debug;
use Doctrine\Common\Collections\Criteria;

use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Reader\ArrayReader;
use Ddeboer\DataImport\Reader\OneToManyReader;
use Ddeboer\DataImport\Writer\ArrayWriter;
use Ddeboer\DataImport\Writer\CallbackWriter;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use Ddeboer\DataImport\ItemConverter\MappingItemConverter;
use Ddeboer\DataImport\ItemConverter\NestedMappingItemConverter;
use Ddeboer\DataImport\ValueConverter\DateTimeValueConverter;

class ControllerQCPurchaseOrder extends QCController {
	// TODO: Some of these properties should be moved to the model and accessed in controller
	// I've just copied and pasted them into the model(s) for now..
	protected $tableName = 'qctr_purchase_order';
	protected $joinTableName = 'order';
	protected $joinCol = 'order_id';
	protected $foreign = 'PurchaseOrder';
	protected $foreignType = 'transaction';

	function __construct($registry) {
		$this->registry = $registry;
		$this->registry->set('tax', new Tax($registry));
		$this->registry->set('lines', new Lines($registry)); // Lines requires tax, load in order

		parent::__construct($registry);
		parent::before();
	}

	// TODO: I can make a generic one of these, just copying for now...
	protected function getService() {}

	public function getStockStatus($status_id = null) {
		$this->load->model('localisation/stock_status');

		$statuses = $this->model_localisation_stock_status->getStockStatuses();

		foreach ($statuses as $status) {
			if ($status['stock_status_id'] == $status_id)
				return $status;
		}
	}

	public function getStockStatusByName($name = '') {
		$this->load->model('localisation/stock_status');

		$statuses = $this->model_localisation_stock_status->getStockStatuses();

		foreach ($statuses as $status) {
			if ($status['name'] == $name)
				return $status;
		}
	}

	public function getStockStatuses($stockStatusId) {
		$this->load->model('localisation/stock_status');
		return $this->model_localisation_stock_status->getStockStatuses();
	}

	public function getTaxClass($taxClassId = null) {
		$this->load->model('localisation/tax_class');

		$classes = $this->model_localisation_tax_class->getTaxClasses();

		foreach ($classes as $class) {
			if ($class['tax_class_id'] == $taxClassId)
				return $class;
		}
	}

	public function getTaxClassByName($name = '') {
		$this->load->model('localisation/tax_class');

		$classes = $this->model_localisation_tax_class->getTaxClasses();

		foreach ($classes as $class) {
			if ($class['title'] == $name)
				return $class;
		}
	}

	/**
	 * Heavy batch operations should be changed to use XMLWriter or
	 * something that doesn't have to load everything into memory?
	 * This should be fine for small or medium-sized stores anyway
	 */
	public function fetch() {
		$output = [];

		$items = $this->getCollection();

		$xml = '<entities>';
		foreach ($items as $item) {
			$xml .= $item->asXML(); // TODO: asIDSXML() is the one to use
			//print('Item Id=' . $item->getId() . ' is named: ' . $item->getName() . '<br>');
		}
		$xml .= '</entities>';

		//echo $xml;
		//exit;

		// TODO: Test vs schema to make sure def matches what Doctrine needs
		$converter = null;
		// Build mappings
		$converters = [];
		$mappings = [];

		// DO products
		EntityMapper::mapEntities($this->em, 'PurchaseOrder', $this->mapXml, $mappings);

		// Returns properties of current node not including... ./*[not(name()=\'Network\')]
		$filtered = EntityMapper::filterEntities($xml, '*[name() = "PurchaseOrder"]');

		$data = XML2Array::createArray($filtered); // Just filters crap out
		$data = (!empty($data['entities'])) ? $data['entities']['PurchaseOrder'] : array();

		$purchaseOrderReader = new ArrayReader($data); // OK for single level
		//$descriptionReader = new ArrayReader($data);
		//$purchaseOrderDescriptionReader = new OneToManyReader($purchaseOrderReader, $descriptionReader, 'description', 'ID', 'ID');

		//$workflow = new Workflow($purchaseOrderDescriptionReader);
		$workflow = new Workflow($purchaseOrderReader);
		$output = [];

		// Adapter specific
		$purchaseOrderWriter = new DoctrineWriter($this->em, 'OcPurchaseOrder');
		//$descriptionWriter = new DoctrineWriter($em, 'App\Entity\OpenCart\PurchaseOrderDescription');
		//$workflow->addWriter(new ArrayWriter($output));
		//$workflow->addWriter(new DoctrineWriter($em, 'App\Entity\OpenCart\PurchaseOrder'));

		$this->load->model('rest/restadmin');

		/*try {
			$dql = $this->em->createQueryBuilder()
				->select(array('pd'))
				->from('OcPurchaseOrderDescription', 'pd')
				->where('pd.language = :language')
				->andWhere('pd.invoice = :invoice')
				->setParameter('language', 1)
				->setParameter('invoice', 77);
				
			$result = $dql->getQuery()->getResult();
		}*/


		$workflow->addWriter(new CallbackWriter(function ($row) use ($mappings, &$purchaseOrderWriter) {
			$fields = $mappings['PurchaseOrder']['fields'];
			$data = array();

			foreach (array_intersect_key($row, $fields) as $prop => $value) {
				if (array_key_exists($prop, $fields)) {
					$data[$fields[$prop]] = $value;
				}
			}

			$p = ObjectFactory::createEntity($this->em, 'OcPurchaseOrder', $data);
			$pd = ObjectFactory::createEntity($this->em, 'OcPurchaseOrderDescription', $data);

			// Shared value - reassign
			// TODO: Need to make a way to assign the same input to multiple fields... 
			// this is getting wiped out when I do the array flip
			$pd['name'] = $pd['description'];
			$pd['meta_title'] = $pd['description'];
			$pd['meta_description'] = $pd['description'];
			$pd['meta_keyword'] = '';
			$pd['tag'] = '';

			$stock_status = $this->getStockStatusByName('In Stock');
			$p['stock_status_id'] = (int)$stock_status['stock_status_id']; // TODO: Set based on if quantity exists

			$taxClass = $this->getTaxClassByName('Taxable Goods');
			$p['tax_class_id'] = (int)$taxClass['tax_class_id']; // TODO: Set based on if quantity exists

			$p['purchase_order_description'] = array();
			array_push($p['purchase_order_description'], $pd);

			//var_dump($p);

			//return;

			$purchaseOrderId = $this->model_rest_restadmin->add($p);
		}));

		$workflow->process();
	}

	/**
	 * return QuickBooks_IPP_Object_PurchaseOrder
	 */
	public function get($id = 4, $data = array()) {
		$itemService = new QuickBooks_IPP_Service_PurchaseOrder();

		// Get the existing item 
		$items = $itemService->query($this->Context, $this->realm, "SELECT * FROM PurchaseOrder WHERE Id = '" . $id . "'");
		$item = ($items && count($items) > 0) ? $items[0] : null;

		return $item;
	}

	// Do not delete -- will be moved to unit tests
	// TODO: Or called from the QC module admin in test section
	public function convertOrder() {
		// Create a blank purchase order transaction
		$this->load->model('resource/transaction');
		$tModel = &$this->model_resource_transaction;
		// TransactionPurchaseOrder extends TransactionBase(Controller $context, $id = null)
		$tModel->setTransactionType(new TransactionPurchaseOrder($this));

		// Get the OpenCart model and con
		$tModel->convert($this->request->get['order_id']);

		$this->response->redirect($this->url->link('transaction/purchase_order', 'token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'], 'SSL'));
	}

	/**
	 * @param $purchaseOrderId
	 */
	public function add($purchaseOrderId = 0) {
		//echo '<pre>';

		$mappings = [];
		$export = false; // Saves a step later

		EntityMapper::mapEntities($this->em, 'PurchaseOrder', $this->mapXml, $mappings, $export);
		EntityMapper::mapEntities($this->em, 'Customer', $this->mapXml, $mappings, $export);
		EntityMapper::mapEntities($this->em, 'Address', $this->mapXml, $mappings, $export);
		EntityMapper::mapEntities($this->em, 'Line', $this->mapXml, $mappings, $export);

		$meta = $this->em->getClassMetadata('OcPurchaseOrder');
		$ilMeta = $this->em->getClassMetadata('OcPurchaseOrderLine');

		$iService = new \App\ResourceService\PurchaseOrder($this->em, 'OcPurchaseOrder');
		$iEntity = $iService->getEntity($purchaseOrderId, false);

		if (!$iEntity) {
			throw new Exception('Could not find the entity with ID: ' . $purchaseOrderId);
		}

		$i = $iService->serializeEntity($iEntity, true); // TODO: Shouldn't need to do this, but I'll find a way to not serialize later

		$ilCollection = $iEntity->getLines();

		//Debug::dump($ilCollection);

		$pService = new \App\Resource\Product($this->em, 'OcProduct');
		$tcService = new \App\Resource\TaxClass($this->em, 'OcTaxClass');
		$entityService = new QuickBooks_IPP_Service_PurchaseOrder();
		$entity = new QuickBooks_IPP_Object_PurchaseOrder();

		$this->fillEntity($entity, $mappings['PurchaseOrder']['fields'], $meta, $i);
		$this->fillEntityObjects('PurchaseOrder', $entity, $mappings, $meta, $i);
		$this->fillEntityRefs($entity, $mappings['PurchaseOrder']['refs'], $i);

		$customer = $iEntity->getCustomer();
		$customerId = $this->getFeedId($customer->getCustomerId(), 'qcli_customer');

		$entity->setCustomerRef($customerId);

		$ln = 1;
		$ilArray = array();

		foreach ($ilCollection as $ilEntity) {
			$taxCodeRef = null;
			$taxCodeTitle = null;

			$il = $iService->serializeEntity($ilEntity);

			$lineEntity = new QuickBooks_IPP_Object_Line();
			$this->fillEntity($lineEntity, $mappings['Line']['fields'], $ilMeta, $il); // Populate entity data
			$this->fillEntityObjects('Line', $lineEntity, $mappings, $ilMeta, $il);

			$taxClass = $ilEntity->getTaxClass(); // TODO: Lazy load!
			if ($taxClass instanceof OcTaxClass) {
				// Trigger load
				$taxCodeTitle = $taxClass->getTitle();
				if ($taxClass->getTaxClassId() != null) {
					$taxCodeRef = $this->getFeedId($taxClass->getTaxClassId(), 'qcli_tax_code'); // Hard-coded to GST
				}
			}

			switch ($il['detailType']) {
				case 'DescriptionOnlyLineDetail':
					if (empty($taxCodeRef)) {
						$taxCodeRef = $this->getFeedId(4, 'qcli_tax_code');  // TODO: Hard-coded to Exempt, use default in QC Admin
					}

					$detail = $lineEntity->getSalesItemLineDetail(); // If a sales item line exists we want to fry it
					if (isset($detail)) $lineEntity->remove('SalesItemLineDetail'); // Remove if it exists
					unset($detail); // Clear var

					$lineEntity->setDetailType('DescriptionOnly');

					$detail = $lineEntity->getDescriptionLineDetail();
					if (!$detail) {
						$detail = new QuickBooks_IPP_Object_DescriptionLineDetail();
						$detail->setTaxCodeRef($taxCodeRef);

						$lineEntity->addDescriptionLineDetail($detail);
					} else {
						$detail->setTaxCodeRef($taxCodeRef);
					}

					break;

				case 'DescriptionItemLineDetail':
					//$tax = $ilEntity->getTax();

					$detail = $lineEntity->getDescriptionLineDetail(); // If a description line exists we want to fry it
					if (isset($detail)) $lineEntity->remove('DescriptionLineDetail'); // Remove if it exists
					unset($detail); // Clear var

					$lineEntity->setDetailType('SalesItemLineDetail');

					$detail = $lineEntity->getSalesItemLineDetail();
					if (!$detail) {
						$detail = new QuickBooks_IPP_Object_SalesItemLineDetail();

						$detail->setTaxCodeRef($taxCodeRef);

						$lineEntity->addSalesItemLineDetail($detail);
					} else {
						$detail->setTaxCodeRef($taxCodeRef);
					}

					break;

				case 'SalesItemLineDetail':
					$detail = $lineEntity->getDescriptionLineDetail(); // If a description line exists we want to fry it
					if (isset($detail)) $lineEntity->remove('DescriptionLineDetail'); // Remove if it exists
					unset($detail); // Clear var

					$lineEntity->setDetailType('SalesItemLineDetail');

					$detail = $lineEntity->getSalesItemLineDetail();
					if (!$detail) {
						$detail = new QuickBooks_IPP_Object_SalesItemLineDetail();

						$productId = (isset($il['product'])) ? $il['product']['productId'] : false;

						if (!$productId) {
							throw new Exception('Product ID not found for line with SalesItemLineDetail detail type!');
						} else {
							$p = $pService->getEntity($productId, false);
							if ($p != null) {
								if (empty($taxCodeRef)) {
									$taxCode = $p->getTaxClass();
									$taxClassId = $taxCode->getTaxClassId();

									$taxCodeRef = $this->getFeedId($taxClassId, 'qcli_tax_code');
								}
							}
						}

						//var_dump('We have a product with ID: ' . $productId);

						$detail->setItemRef($this->getFeedId($productId, 'qcli_product'));
						$detail->setTaxCodeRef($taxCodeRef);

						$lineEntity->addSalesItemLineDetail($detail);
					} else {
						$productId = (isset($il['product'])) ? $il['product']['productId'] : false;

						if (!$productId) {
							throw new Exception('Product ID not found for line with SalesItemLineDetail detail type!');
						} else {
							$p = $pService->getEntity($productId, false);
							if ($p != null) {
								if (empty($taxCodeRef)) {
									$taxCode = $p->getTaxClass();
									$taxClassId = $taxCode->getTaxClassId();
									//$taxCodeTitle = $taxCode->getTitle();

									$taxCodeRef = $this->getFeedId($taxClassId, 'qcli_tax_code');
								}
							}
						}

						//var_dump('We have a product with ID: ' . $productId);

						$detail->setItemRef($this->getFeedId($productId, 'qcli_product'));
						$detail->setTaxCodeRef($taxCodeRef);
					}

				case 'CommissionLineDetail':
					$detail = $lineEntity->getDescriptionLineDetail(); // If a description line exists we want to fry it
					if (isset($detail)) $lineEntity->remove('DescriptionLineDetail'); // Remove if it exists
					unset($detail); // Clear var

					$lineEntity->setDetailType('SalesItemLineDetail');

					$detail = $lineEntity->getSalesItemLineDetail();
					if (!$detail) {
						$detail = new QuickBooks_IPP_Object_SalesItemLineDetail();

						$detail->setTaxCodeRef($taxCodeRef);
						$detail->setUnitPrice((float)$ilEntity->getTotal() / (float)$detail->getQuantity());

						$lineEntity->addSalesItemLineDetail($detail);
					} else {
						$detail->setTaxCodeRef($taxCodeRef);
						$detail->setUnitPrice((float)$ilEntity->getTotal() / (float)$ilEntity->getQuantity());
					}

					break;
			}

			$ilArray[$ln] = $il;

			$lineEntity->setLineNum($ln);
			$entity->addLine($lineEntity);

			$ln++;
		}

		$entity->setTxnDate($iEntity->getPurchaseOrderDate()->format('Ylmn-m-d'));
		$entity->setDueDate($iEntity->getDueDate()->format('Y-m-d'));

		$entity->setOcId($purchaseOrderId);

		$this->export($entityService, $entity, false);
	}

	/**
	 * @param $purchaseOrderId
	 */
	public function edit($purchaseOrder = null, $feedEntity = false) {
		$mappings = [];
		$export = false; // Saves a step later
		//$purchaseOrderId = 9;

		EntityMapper::mapEntities($this->em, 'PurchaseOrder', $this->mapXml, $mappings, $export);
		EntityMapper::mapEntities($this->em, 'Customer', $this->mapXml, $mappings, $export);
		EntityMapper::mapEntities($this->em, 'Address', $this->mapXml, $mappings, $export);
		EntityMapper::mapEntities($this->em, 'Line', $this->mapXml, $mappings, $export);

		$meta = $this->em->getClassMetadata('OcPurchaseOrder');
		$ilMeta = $this->em->getClassMetadata('OcPurchaseOrderLine');

		$iService = new \App\Resource\PurchaseOrder($this->em, 'OcPurchaseOrder');

		$purchaseOrderId = false;
		$iEntity = null;

		if ($purchaseOrder instanceof OcPurchaseOrder) {
			$purchaseOrderId = $purchaseOrder->getPurchaseOrderId();
			$iEntity = $purchaseOrder;
		} elseif (is_numeric($purchaseOrder) && (int)$purchaseOrder > 0) {
			$purchaseOrderId = (int)$purchaseOrder;
			$iEntity = $iService->getEntity($purchaseOrderId, false);
		}

		$i = $iService->serializeEntity($iEntity, true); // TODO: Shouldn't need to do this, but I'll find a way to not serialize later

		$ilCollection = $iEntity->getLines();

		$pService = new \App\Resource\Product($this->em, 'OcProduct');
		$tcService = new \App\Resource\TaxClass($this->em, 'OcTaxClass');
		$entityService = new QuickBooks_IPP_Service_PurchaseOrder();
		$entity = new QuickBooks_IPP_Object_PurchaseOrder();
		$feedId = 0;

		// Set our $feedId and $entity variables - this process is repeated in most QCController classes
		// This method handles the log
		$this->setRemoteEntityVars($feedId, $entity, $purchaseOrderId, $feedEntity);

		$entity->setOcId($purchaseOrderId);

		$count = $entity->countLine(); // Keith's lib won't let me iterate with foreach?

		if ($count && $count > 0) {
			// Fry any existing lines - not really any point in keeping them
			for ($idx = 0; $idx < $count; $idx++) {
				$line = $entity->getLine($idx);

				$entity->unsetLine($line);
			}
		}

		try {
			$this->fillEntity($entity, $mappings['PurchaseOrder']['fields'], $meta, $i);
			$this->fillEntityObjects('PurchaseOrder', $entity, $mappings, $meta, $i);
			$this->fillEntityRefs($entity, $mappings['PurchaseOrder']['refs'], $i);
		} catch (Exception $e) {
			$error = $e;
			$error = $e;
		}

		$customer = $iEntity->getCustomer();
		$customerId = $this->getFeedId($customer->getCustomerId(), 'qcli_customer');

		$entity->setCustomerRef($customerId);

		$ln = 1;
		$ilArray = array();

		foreach ($ilCollection as $ilEntity) {
			$taxCodeRef = null;
			$taxCodeTitle = null;

			$il = $iService->serializeEntity($ilEntity);

			$lineEntity = new QuickBooks_IPP_Object_Line();
			$this->fillEntity($lineEntity, $mappings['Line']['fields'], $ilMeta, $il); // Populate entity data
			$this->fillEntityObjects('Line', $lineEntity, $mappings, $ilMeta, $il);

			$taxClass = $ilEntity->getTaxClass(); // TODO: Lazy load!
			if ($taxClass instanceof OcTaxClass) {
				// Trigger load
				$taxCodeTitle = $taxClass->getTitle();
				if ($taxClass->getTaxClassId() != null) {
					$taxCodeRef = $this->getFeedId($taxClass->getTaxClassId(), 'qcli_tax_code'); // Hard-coded to GST
				}
			}

			switch ($il['detailType']) {
				case 'DescriptionOnlyLineDetail':
					if (empty($taxCodeRef)) {
						$taxCodeRef = $this->getFeedId(4, 'qcli_tax_code');  // TODO: Hard-coded to Exempt, use default in QC Admin
					}

					$detail = $lineEntity->getSalesItemLineDetail(); // If a sales item line exists we want to fry it
					if (isset($detail)) $lineEntity->remove('SalesItemLineDetail'); // Remove if it exists
					unset($detail); // Clear var

					$lineEntity->setDetailType('DescriptionOnly');

					$detail = $lineEntity->getDescriptionLineDetail();
					if (!$detail) {
						$detail = new QuickBooks_IPP_Object_DescriptionLineDetail();
						$detail->setTaxCodeRef($taxCodeRef);

						$lineEntity->addDescriptionLineDetail($detail);
					} else {
						$detail->setTaxCodeRef($taxCodeRef);
					}

					break;

				case 'DescriptionItemLineDetail':
					//$tax = $ilEntity->getTax();

					$detail = $lineEntity->getDescriptionLineDetail(); // If a description line exists we want to fry it
					if (isset($detail)) $lineEntity->remove('DescriptionLineDetail'); // Remove if it exists
					unset($detail); // Clear var

					$lineEntity->setDetailType('SalesItemLineDetail');

					$detail = $lineEntity->getSalesItemLineDetail();
					if (!$detail) {
						$detail = new QuickBooks_IPP_Object_SalesItemLineDetail();

						$detail->setTaxCodeRef($taxCodeRef);

						$lineEntity->addSalesItemLineDetail($detail);
					} else {
						$detail->setTaxCodeRef($taxCodeRef);
					}

					break;

				case 'SalesItemLineDetail':
					$detail = $lineEntity->getDescriptionLineDetail(); // If a description line exists we want to fry it
					if (isset($detail)) $lineEntity->remove('DescriptionLineDetail'); // Remove if it exists
					unset($detail); // Clear var

					$lineEntity->setDetailType('SalesItemLineDetail');

					$detail = $lineEntity->getSalesItemLineDetail();
					if (!$detail) {
						$detail = new QuickBooks_IPP_Object_SalesItemLineDetail();

						$productId = (isset($il['product'])) ? $il['product']['productId'] : false;

						if (!$productId) {
							throw new Exception('Product ID not found for line with SalesItemLineDetail detail type!');
						} else {
							$p = $pService->getEntity($productId, false);
							if ($p != null) {
								if (empty($taxCodeRef)) {
									$taxCode = $p->getTaxClass();
									$taxClassId = $taxCode->getTaxClassId();

									$taxCodeRef = $this->getFeedId($taxClassId, 'qcli_tax_code');
								}
							}
						}

						//var_dump('We have a product with ID: ' . $productId);

						$detail->setItemRef($this->getFeedId($productId, 'qcli_product'));
						$detail->setTaxCodeRef($taxCodeRef);

						$lineEntity->addSalesItemLineDetail($detail);
					} else {
						$productId = (isset($il['product'])) ? $il['product']['productId'] : false;

						if (!$productId) {
							throw new Exception('Product ID not found for line with SalesItemLineDetail detail type!');
						} else {
							$p = $pService->getEntity($productId, false);
							if ($p != null) {
								if (empty($taxCodeRef)) {
									$taxCode = $p->getTaxClass();
									$taxClassId = $taxCode->getTaxClassId();
									//$taxCodeTitle = $taxCode->getTitle();

									$taxCodeRef = $this->getFeedId($taxClassId, 'qcli_tax_code');
								}
							}
						}

						//var_dump('We have a product with ID: ' . $productId);

						$detail->setItemRef($this->getFeedId($productId, 'qcli_product'));
						$detail->setTaxCodeRef($taxCodeRef);
					}

					break;

				case 'CommissionLineDetail':
					$detail = $lineEntity->getDescriptionLineDetail(); // If a description line exists we want to fry it
					if (isset($detail)) $lineEntity->remove('DescriptionLineDetail'); // Remove if it exists
					unset($detail); // Clear var

					$lineEntity->setDetailType('SalesItemLineDetail');

					$detail = $lineEntity->getSalesItemLineDetail();
					if (!$detail) {
						$detail = new QuickBooks_IPP_Object_SalesItemLineDetail();

						$detail->setTaxCodeRef($taxCodeRef);
						$detail->setUnitPrice((float)$ilEntity->getTotal() / (float)$detail->getQuantity());

						$lineEntity->addSalesItemLineDetail($detail);
					} else {
						$detail->setTaxCodeRef($taxCodeRef);
						$detail->setUnitPrice((float)$ilEntity->getTotal() / (float)$ilEntity->getQuantity());
					}

					break;
			}

			$ilArray[$ln] = $il;

			$lineEntity->setLineNum($ln);
			$entity->addLine($lineEntity);

			$ln++;
		}

		// Set transaction tax detail
		/*$taxDetail = new QuickBooks_IPP_Object_TxnTaxDetail();
		$taxDetail->setTxnTaxCodeRef($this->getFeedId(1, 'qcli_tax_code')); // Hard-coded to GST) // Auto charge GST

		$taxes = $this->lines->getTaxes();

		$totalTax = 0.00;
		foreach ($taxes as $taxId => $taxTotal) {
			$totalTax += $taxTotal;
		};

		$taxDetail->setTotalTax($totalTax);

		$taxLine = new QuickBooks_IPP_Object_TaxLine();
		$taxLine->setDetailType('TaxLineDetail');
		$taxLine->setAmount($totalTax);

		$taxLineDetail = new QuickBooks_IPP_Object_TaxLineDetail();
		$taxLineDetail->setTaxRateRef(9); // Auto set 5% rate
		$taxLineDetail->setPercentBased('true');
		$taxLineDetail->setTaxPercent(5); // TODO: Pull from ID = 6 (5% GST) tax rate
		$taxLineDetail->setNetAmountTaxable($totalTax / 0.05); // TODO: Don't hard-code!

		$taxLine->setTaxLineDetail($taxLineDetail);
		$taxDetail->setTaxLine($taxLine);

		$entity->setTransactionTaxDetail($taxDetail);*/

		$entity->setTxnDate($iEntity->getPurchaseOrderDate()->format('Y-m-d'));
		$entity->setDueDate($iEntity->getDueDate()->format('Y-m-d'));

		$entity->setOcId($purchaseOrderId);

		$this->export($entityService, $entity, false);
	}

	/**
	 * Proxy method allows for stronger type hinting
	 */
	protected function export (QuickBooks_IPP_Service_PurchaseOrder &$service, QuickBooks_IPP_Object_PurchaseOrder &$item, $asXml = false) {
		$this->_export($service, $item, $asXml);
	}

	/**
	 * Event hook triggered before adding a purchase order
	 */
	public function eventBeforeAddPurchaseOrder($purchaseOrderId) {

	}

	/**
	 * Event hook triggered after adding a purchase order
	 */
	public function eventAfterAddPurchaseOrder($purchaseOrderId) {
		if ($this->quickbooks_is_connected) {
			// Post purchase order to QBO
			$this->add($purchaseOrderId);
		} else {
			$errorDetail = array(
				'error' => 'QuickBooks is not connected'
			);

			$this->session->data['ipp_error']['warning'] = $errorDetail;
		}
	}

	/**
	 * Event hook triggered before editing a purchase order
	 */
	public function eventBeforeEditPurchaseOrder() {

	}

	/**
	 * Event hook triggered after editing a purchase order
	 */
	public function eventAfterEditPurchaseOrder($purchaseOrderId) {
		if ($this->quickbooks_is_connected) {
			// Post changes to QBO
			$this->edit($purchaseOrderId);
		} else {
			$errorDetail = array(
				'error' => 'QuickBooks is not connected'
			);

			$this->session->data['ipp_error']['warning'] = $errorDetail;
		}
	}

	/*public function eventOnDeletePurchaseOrder() {
		
	}*/

	/**
	 * This creates a qcli_{tablename} entry in the database
	 * Property name specifies entity field to use for OcId
	 * Slightly different implementation for transaction stuff
	 */
	protected function _writeListItem(&$entity, $propertyName = null) {
		//I Could implement this as a callback; this query is only necessary because I can't map on SKU field
		// For most feeds I don't think this will be necessary, as I should be able to map data entirely based on the entity mappings provided
		$feedId = self::qbId($entity->getId());

		// TODO: Don't I already have a standard way of doing this?
		if (is_string($propertyName) && !empty($propertyName)) {
			$ocId = $entity->{'get' . ucwords($propertyName)}();
		} else {
			$ocId = $entity->getOcId();
		}

		if ($feedId > 0 && $ocId > 0) {
			// We need parent ref and ref name to do updates on sub-entities and they don't map to anything in OC or QC right now
			$refName = $entity->getFullyQualifiedName();
			$refId = self::qbId($entity->getParentRef());

			$query = "UPDATE " . DB_PREFIX . $this->tableName . " SET feed_id = '" . $feedId . "', oc_entity_id = '" . $ocId . "' WHERE purchase_order_id = '" . $ocId . "'";

			$query = $this->db->query($query);

			// Store references
			// We're at least reusing the entity...
			// Mapping generation needs to be moved to _before impl. in the extending controller class
			if (isset($this->mappings[$this->foreign]['refs'])) {
				$this->updateEntityRefs($entity, $this->mappings[$this->foreign]['refs']); // Populate entity data
			}
		} else {
			//print('Invalid mapping for ' . $entity->getName() . ': feedId => ' . $feedId . ', product_id => ' .  $ocId);
			//var_dump($entity);

		}
	}
}