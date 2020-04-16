<?php

namespace App\Models;

use App\Models\Upload\Image;
use App\Traits\CompactHelper;
use App\Traits\GeospatialHelper;
use App\Traits\ImageHelper;
use App\Traits\UploadConst;
use App\Traits\UploadHelper;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Inventory extends Model
{
    protected $table = 'inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inventory_id',
        'entity_type_id',
        'dealer_id',
        'dealer_location_id',
        'created_at',
        'updated_at',
        'active',
        'title',
        'stock',
        'manufacturer',
        'brand',
        'model',
        'description',
        'video_embed_code',
        'category',
        'vin',
        'geolocation',
        'msrp',
        'price',
        'use_website_price',
        'website_price',
        'dealer_price',
        'monthly_payment',
        'year',
        'condition',
        'length',
        'width',
        'height',
        'weight',
        'gvwr',
        'axle_capacity',
        'cost_of_unit',
        'cost_of_shipping',
        'cost_of_prep',
        'total_of_cost',
        'minimum_selling_price',
        'notes',
        'is_sold',
        'is_special',
        'is_featured',
        'show_on_ksl',
        'show_on_racingjunk',
        'show_on_website',
        'overlay_enabled',
        'status',
        'is_consignment',
        'is_archived',
        'sales_price',
        'height_display_mode',
        'width_display_mode',
        'length_display_mode',
        'height_inches',
        'width_inches',
        'length_inches',
        'show_on_rvtrader',
        'payload_capacity',
        'chosen_overlay'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * Updates the inventory_update table when needed.
     *
     * @param $type string 'insert', 'update', 'delete', 'unarchive', 'archive', etc
     */
    public function updateTable($type, $force_location_id = false)
    {
        //TODO for what?
        if ($type != 'delete' && $type != 'delete-update') {
            $action = 'update';
        } else {
            $action = 'delete';
        }

        if ($force_location_id !== false) {
            $location_id = $force_location_id;
        } else {
            $location_id = $this->getData('dealer_location_id');
        }

        $sql = "REPLACE INTO `inventory_update` SET `inventory_id` = :inventoryId, `dealer_id` = :dealerId, stock = :stock, location_id = :location_id, action = :action, specific_action = :specificAction, time_entered = :time, processed = 0";
        $stmt = Db_Manager::getConnection()->prepare($sql);
        $stmt->execute(array(
            'inventoryId' => $this->getData('inventory_id'),
            'dealerId' => $this->getData('dealer_id'),
            'stock' => $this->getData('stock'),
            'location_id' => $location_id,
            'action' => $action,
            'specificAction' => $type,
            'time' => time(),
        ));
    }

    public function load($id)
    {
        //TODO for what?
        // need to overwrite this, as we need special treament for dates and geolocation (binary format)
        $query = new Db_Query($this->getTableName());
        $query->add($this->getIdFieldName(), $id);

        $columns = array(
            "inventory_id",
            "entity_type_id",
            "dealer_id",
            "dealer_location_id",
            "CONCAT(DATE_FORMAT(created_at, '%Y-%m-%dT%T'), TIME_FORMAT(NOW() - UTC_TIMESTAMP(), '%H%i')) AS created_at",
            "CONCAT(DATE_FORMAT(updated_at, '%Y-%m-%dT%T'), TIME_FORMAT(NOW() - UTC_TIMESTAMP(), '%H%i')) AS updated_at",
            "active",
            "title",
            //"attributes",
            "stock",
            "manufacturer",
            "brand",
            "model",
            "description",
            'video_embed_code',
            "category",
            "vin",
            "AsBinary(geolocation) as geolocation",
            "msrp",
            "msrp_min",
            "price",
            "use_website_price",
            "website_price",
            "dealer_price",
            "monthly_payment",
            "year",
            "condition",
            "length",
            "width",
            "height",
            "weight",
            "gvwr",
            "axle_capacity",
            'cost_of_unit',
            'cost_of_shipping',
            'cost_of_prep',
            'total_of_cost',
            'minimum_selling_price',
            'notes',
            'is_special',
            'is_featured',
            'show_on_ksl',
            'show_on_racingjunk',
            'show_on_website',
            'overlay_enabled',
            'status',
            'is_consignment',
            'is_archived',
            'sales_price',
            'height_display_mode',
            'width_display_mode',
            'length_display_mode',
            'height_inches',
            'width_inches',
            'length_inches',
            'show_on_rvtrader',
            'payload_capacity',
            'chosen_overlay'
        );
        $query->setColumns($columns);

        $data = $query->doSelect();
        while ($row = $data->fetch(PDO::FETCH_OBJ)) {
            foreach ($row as $key => $value) {
                $this->setData($key, $value);
            }
        }

        $entity_type_id = $this->getData('entity_type_id');
        if (!isset($entity_type_id)) {
            return false;
        }


        // get attributes for entity_type
        $attributes = array();
        $attributevalues = array();

        $attr_query = new Db_Query('eav_attribute');
        $attr_query->join('eav_entity_type_attribute', 'eav_attribute.attribute_id = eav_entity_type_attribute.attribute_id');
        $attr_query->add('eav_entity_type_attribute.entity_type_id', $this->getData('entity_type_id'));
        $attr_query->order('eav_entity_type_attribute.sort_order', 'ASC');
        $attr_query->setColumns(array('eav_attribute.attribute_id', 'eav_attribute.code'));

        $attr_data = $attr_query->doSelect();
        while ($row = $attr_data->fetch(PDO::FETCH_OBJ)) {
            $attributes[$row->attribute_id] = $row->code;
        }

        $attr_val_query = new Db_Query('eav_attribute_value');
        $attr_val_query->add('inventory_id', $this->getData($this->_idFieldName));
        $attr_val_query->setColumns(array('attribute_id', 'value'));

        $attr_val_data = $attr_val_query->doSelect();
        while ($row = $attr_val_data->fetch(PDO::FETCH_OBJ)) {
            if (isset($attributes[$row->attribute_id])) {
                $attributevalues[$attributes[$row->attribute_id]] = $row->value;
            }
        }

        $this->setData('attributes', $attributevalues);


        // get custom fields for craigslist
        $clappFields = array();

        $clapp_query = new Db_Query('inventory_clapp');
        $clapp_query->add('inventory_id', $this->getData($this->_idFieldName));

        $clapp_data = $clapp_query->doSelect();
        while ($row = $clapp_data->fetch(PDO::FETCH_OBJ)) {
            if (!empty($row->value)) {
                $clappFields[$row->field] = $row->value;
            }
        }

        $this->setData('craigslist', $clappFields);

        $this->_origData = $this->_data;

        if ($data->rowCount() > 0) {
            return $this;
        } else {
            return false;
        }
    }

    protected function _insert()
    {
        $now = new DateTime();
        $this->setData('created_at', $now->format('Y-m-d H:i:s'));

        // get attributes for this entity type
        $defaultattributes = array();
        $attr_query = new Db_Query('eav_attribute');
        $attr_query->join('eav_entity_type_attribute', 'eav_attribute.attribute_id = eav_entity_type_attribute.attribute_id');
        $attr_query->add('eav_entity_type_attribute.entity_type_id', $this->getData('entity_type_id'));
        $attr_query->order('eav_entity_type_attribute.sort_order', 'ASC');
        $attr_query->setColumns(array(
            'eav_attribute.attribute_id',
            'eav_attribute.code',
            'eav_attribute.name',
            'eav_attribute.type',
            'eav_attribute.values'
        ));

        $attr_data = $attr_query->doSelect();
        while ($row = $attr_data->fetch(PDO::FETCH_OBJ)) {
            $defaultattributes[$row->attribute_id] = $row->code;
        }

        $statement = new Db_Query_Statement(Db_Manager::getConnection());

        $fields = array();
        $values = array();
        $placeholders = array();

        // inventory data not specific to type (a.k.a not attributes)
        foreach ($this->_data as $name => $value) {
            if (in_array($name, $this->_allowedFields)) {
                $fields[] = $statement->getConnection()->quoteIdentifier($name);

                switch ($name) {
                    case "geolocation":
                        $placeholders[] = 'PointFromText(?)';
                        $geometry = Helper_Geospatial::FromWKB($value);
                        $values[] = 'Point(' . $geometry['lat'] . ' ' . $geometry['lon'] . ')';
                        break;
                    case "description":
                        $placeholders[] = '?';
                        $values[] = Helper_Sanitize::stripMultipleWhitespace(Helper_Sanitize::removeTypographicCharacters($value));
                        break;
                    default:
                        $placeholders[] = '?';
                        $values[] = $value;
                        break;
                }
            }
        }

        try {
            $queryString = "INSERT INTO `{$this->_tableName}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ");";

            file_put_contents('/var/www/vhosts/trailercentral.com/html/test.txt', $this->_data['stock'] . ':' . $queryString . "\n\n", FILE_APPEND);

            $statement->setString($queryString);
            $statement->setParams($values);

            $paramSize = implode('', $values);
            $paramSize = strlen($paramSize);
            file_put_contents('/var/www/vhosts/trailercentral.com/html/test.txt', $this->_data['stock'] . ': Bound parameter data size is ' . $paramSize . " bytes\n\n", FILE_APPEND);

            Log::debug($statement);
            $statement->bindAndExecute();

            file_put_contents('/var/www/vhosts/trailercentral.com/html/test.txt', $this->_data['stock'] . ': main query completed OK' . "\n\n", FILE_APPEND);

            $id = $statement->getConnection()->lastInsertId();
            $this->setData($this->getIdFieldName(), $id);

            if (count($this->getData('attributes')) > 0 && $this->_data['stock'] !== 'TEST0001') {
                $add_attr_statement = new Db_Query_Statement(Db_Manager::getConnection());
                $statement_string = "INSERT INTO `eav_attribute_value` (" . $add_attr_statement->getConnection()->quoteIdentifier('attribute_id') . ", " . $add_attr_statement->getConnection()->quoteIdentifier('inventory_id') . ", " . $add_attr_statement->getConnection()->quoteIdentifier('value') . ") VALUES (" . implode(', ', array(
                        '?',
                        '?',
                        '?'
                    )) . ");";
                $add_attr_statement->setString($statement_string);

                $attributes = $this->getData('attributes');
                foreach ($attributes as $code => $value) {
                    foreach ($defaultattributes as $attributeid => $attributecode) {
                        if ($code == $attributecode) {
                            $params = array();
                            $params[] = intval($attributeid);
                            $params[] = intval($this->getData($this->getIdFieldName()));
                            $params[] = $value;

                            $add_attr_statement->setParams($params);

                            $result = $add_attr_statement->bindAndExecute();
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("An error occured while inserting data into the database. " . $e->getMessage());
            file_put_contents('/var/www/vhosts/trailercentral.com/html/test.txt', $this->_data['stock'] . ':' . $e->getMessage() . "\n\n", FILE_APPEND);

            throw new Exception("INSERT failed for '" . $this->getIdFieldName() . ":" . $this->getId() . "': " . $e->getMessage(), Resource_Errors_Database::DATABASE_INSERT_FAILED, $e);
        }

        /*
         * Save feature lists
         */

        // Same as eav_attributes, delete old data first -- each time we must completely re-insert the data
        $delete_feature_query = new Db_Query('inventory_feature');
        $delete_feature_query->add('inventory_id', $this->getId());
        $delete_feature_query->doDelete();

        $add_feature_statement = Db_Manager::getConnection()->prepare("INSERT INTO `inventory_feature` (`inventory_id`, `feature_list_id`, `value`) VALUES(:inventoryId, :featureListId, :value)");

        $featureList = $this->getData('features');

        if (count($featureList) > 0) {
            foreach ($featureList as $featureId => $featureValue) {
                foreach ($featureValue as $value) {
                    if (!empty($value)) {
                        $add_feature_statement->execute(array(
                            'inventoryId' => $this->getId(),
                            'featureListId' => $featureId,
                            'value' => $value
                        ));
                    }
                }
            }
        }

        /*
         * Save craigslist custom fields
         */

        // Same as eav_attributes, delete old data first -- each time we must completely re-insert the data
        $delete_clapp_query = new Db_Query('inventory_clapp');
        $delete_clapp_query->add('inventory_id', $this->getId());
        $delete_clapp_query->doDelete();

        $add_craigslist_statement = Db_Manager::getConnection()->prepare("INSERT INTO `inventory_clapp` (`inventory_id`, `field`, `value`) VALUES(:inventoryId, :field, :value)");

        $craigslistFields = $this->getData('craigslist');
        if (count($craigslistFields) > 0) {
            foreach ($craigslistFields as $field => $value) {
                if (!empty($value)) {
                    // Image Processing?
                    if ($field === 'default-image') {
                        // Save Image First
                        unset($craigslistFields[$field]);
                        if (isset($value['new'])) {
                            // Set Primary Image Back Again
                            $value = $this->_uploadImage($value['path']);
                            $craigslistFields[$field] = $value;
                        } // Set Value to Current Field If Not New
                        elseif (!empty($value['file'])) {
                            $value = $value['file'];
                            $craigslistFields[$field] = $value;
                        } else {
                            continue;
                        }
                    }

                    // Value Should NOT Be Array
                    if (is_array($value) || $value === 'Array') {
                        continue;
                    }

                    // Insert
                    $add_craigslist_statement->execute(array(
                        'inventoryId' => $this->getId(),
                        'field' => $field,
                        'value' => $value
                    ));
                }
            }
        }

        $this->updateTable('insert');

        return $this->getData($this->getIdFieldName());
    }

    protected function _update()
    {
        $now = new DateTime();
        $this->setData('updated_at', $now->format('Y-m-d H:i:s'));

        // Images Only?
        if (empty($_GET['images_only'])) {
            $statement = new Db_Query_Statement(Db_Manager::getConnection());

            $queryString = "UPDATE `{$this->_tableName}` SET ";

            foreach ($this->_data as $name => $value) {
                if (in_array($name, $this->_allowedFields)) {
                    if ($value === null || (isset($this->_origData[$name]) && $this->_origData[$name] == $value)) {
                        continue;
                    }
                    $identifier = $statement->getConnection()->quoteIdentifier($name);

                    switch ($name) {
                        case "geolocation":
                            $geometry = Helper_Geospatial::FromWKB($value);
                            $lat = $geometry['lat'];
                            $lon = $geometry['lon'];
                            $queryString .= "{$identifier} = PointFromText('Point({$lat} {$lon})'), ";
                            break;
                        case "description":
                            //$queryString .= "{$identifier} = '" . Helper_Sanitize::stripMultipleWhitespace(Helper_Sanitize::removeTypographicCharacters($value)) . "', ";
                            $queryString .= $identifier . '=' . $statement->getConnection()->quote(Helper_Sanitize::stripMultipleWhitespace(Helper_Sanitize::removeTypographicCharacters($value))) . ', ';
                            break;
                        default:
                            //$queryString .= "{$identifier} = '{$value}', ";
                            $queryString .= $identifier . '=' . $statement->getConnection()->quote($value) . ', ';
                            break;
                    }
                }
            }

            if (strpos($queryString, '=') > 0) {
                $queryString = rtrim($queryString, ', ');
                $queryString .= " WHERE " . $statement->getConnection()->quoteIdentifier($this->getIdFieldName()) . " = '" . $this->getData($this->_idFieldName) . "';";

                try {
                    $statement->setString($queryString);
                    Log::debug($statement);

                    $result = $statement->bindAndExecute();
                    $count = $result->rowCount();
                } catch (Exception $e) {
                    Log::error("An error occured updating the database. " . $e->getMessage());

                    throw new Exception("UPDATE failed for '{$this->getIdFieldName()}:{$this->getId()}'", Resource_Errors_Database::DATABASE_UPDATE_FAILED, $e);
                }
            } else {
                $count = 0;
            }

            // get attributes for this entity type
            $defaultattributes = array();
            $attr_query = new Db_Query('eav_attribute');
            $attr_query->join('eav_entity_type_attribute', 'eav_attribute.attribute_id = eav_entity_type_attribute.attribute_id');
            $attr_query->add('eav_entity_type_attribute.entity_type_id', $this->getData('entity_type_id'));
            $attr_query->order('eav_entity_type_attribute.sort_order', 'ASC');
            $attr_query->setColumns(array(
                'eav_attribute.attribute_id',
                'eav_attribute.code',
                'eav_attribute.name',
                'eav_attribute.type',
                'eav_attribute.values'
            ));

            $attr_data = $attr_query->doSelect();
            while ($row = $attr_data->fetch(PDO::FETCH_OBJ)) {
                $defaultattributes[$row->attribute_id] = $row->code;
            }

            // save attributes
            $attributes = $this->getData('attributes');

            $defaultattributes = array();
            $attributes_to_save = array();

            $attr_query = new Db_Query('eav_attribute');
            $attr_query->join('eav_entity_type_attribute', 'eav_attribute.attribute_id = eav_entity_type_attribute.attribute_id');
            $attr_query->add('eav_entity_type_attribute.entity_type_id', $this->getData('entity_type_id'));
            $attr_query->order('eav_entity_type_attribute.sort_order', 'ASC');
            $attr_query->setColumns(array(
                'eav_attribute.attribute_id',
                'eav_attribute.code',
                'eav_attribute.name',
                'eav_attribute.type',
                'eav_attribute.values'
            ));

            $attr_data = $attr_query->doSelect();
            while ($row = $attr_data->fetch(PDO::FETCH_OBJ)) {
                //$defaultattributes[$row->code] = intval($row->attribute_id);
                $defaultattributes[$row->attribute_id] = $row->code;
            }

            $defaultAttributesToId = array_flip($defaultattributes);

            foreach ($attributes as $attributecode => $attributevalue) {
                if (in_array($attributecode, $defaultattributes)) {
                    $attributes_to_save[] = array(
                        'id' => $defaultAttributesToId[$attributecode],
                        'value' => $attributevalue
                    );
                }
            }

            // create attributes associated with the entity type that do not yet exist for this inventory (set to null)
            //foreach ($defaultattributes as $attributecode => $attributevalue) {
            //    if (!in_array($attributecode, array_keys($attributes))) {
            //        $attributes_to_save[] = array('id' => $attributecode, 'value' => null);
            //    }
            //}

            // delete any existing attribute values - just so we have no weird data or errors due to attempted duplicate entries
            $delete_attr_query = new Db_Query('eav_attribute_value');
            $delete_attr_query->add('inventory_id', $this->getId());
            $delete_attr_query->doDelete();

            $add_attr_statement = new Db_Query_Statement(Db_Manager::getConnection());
            $statement_string = "INSERT INTO `eav_attribute_value` (" . $add_attr_statement->getConnection()->quoteIdentifier('attribute_id') . ", " . $add_attr_statement->getConnection()->quoteIdentifier('inventory_id') . ", " . $add_attr_statement->getConnection()->quoteIdentifier('value') . ") VALUES (" . implode(', ', array(
                    '?',
                    '?',
                    '?'
                )) . ");";
            $add_attr_statement->setString($statement_string);

            foreach ($attributes_to_save as $key => $attribute) {
                $params = array();
                $params[] = intval($attribute['id']);
                $params[] = intval($this->getId());
                $params[] = $attribute['value'];

                $add_attr_statement->setParams($params);

                $result = $add_attr_statement->bindAndExecute();
                $attrcount = $result->rowCount();
            }

            /*
             * Save feature lists
             */

            // Same as eav_attributes, delete old data first -- each time we must completely re-insert the data
            $delete_feature_query = new Db_Query('inventory_feature');
            $delete_feature_query->add('inventory_id', $this->getId());
            $delete_feature_query->doDelete();

            $add_feature_statement = Db_Manager::getConnection()->prepare("INSERT INTO `inventory_feature` (`inventory_id`, `feature_list_id`, `value`) VALUES(:inventoryId, :featureListId, :value)");

            $featureList = $this->getData('features');

            if (count($featureList) > 0) {
                foreach ($featureList as $featureId => $featureValue) {
                    foreach ($featureValue as $value) {
                        if (!empty($value)) {
                            $add_feature_statement->execute(array(
                                'inventoryId' => $this->getId(),
                                'featureListId' => $featureId,
                                'value' => $value
                            ));
                        }
                    }
                }
            }
        }

        /*
         * Save craigslist custom fields
         */

        // Same as eav_attributes, delete old data first -- each time we must completely re-insert the data
        $delete_clapp_query = new Db_Query('inventory_clapp');
        $delete_clapp_query->add('inventory_id', $this->getId());
        $delete_clapp_query->doDelete();

        $add_craigslist_statement = Db_Manager::getConnection()->prepare("INSERT INTO `inventory_clapp` (`inventory_id`, `field`, `value`) VALUES(:inventoryId, :field, :value)");

        $craigslistFields = $this->getData('craigslist');

        if (count($craigslistFields) > 0) {
            foreach ($craigslistFields as $field => $value) {
                if (!empty($value)) {
                    // Image Processing?
                    if ($field === 'default-image') {
                        // Save Image First
                        unset($craigslistFields[$field]);
                        if (isset($value['new'])) {
                            // Set Primary Image Back Again
                            $value = $this->_uploadImage($value['path']);
                            $craigslistFields[$field] = $value;
                        } // Set Value to Current Field If Not New
                        elseif (!empty($value['file'])) {
                            $value = $value['file'];
                            $craigslistFields[$field] = $value;
                        } else {
                            continue;
                        }
                    }

                    // Value Should NOT Be Array
                    if (is_array($value) || $value === 'Array') {
                        continue;
                    }

                    // Insert
                    $add_craigslist_statement->execute(array(
                        'inventoryId' => $this->getId(),
                        'field' => $field,
                        'value' => $value
                    ));
                }
            }
        }

        if ($this->_origData['dealer_location_id'] != $this->_data['dealer_location_id']) {
            // This handles HTW updates and deletes from the old location but re-adds
            // to the new location
            $this->updateTable('delete-update', $this->_origData['dealer_location_id']);
            $this->updateTable('insert-update');
        } else {
            $this->updateTable('update');
        }

        return $count;
    }

    public function _delete()
    {
        // delete the attributes before deleting the object
        $delete_attr_query = new Db_Query('eav_attribute_value');
        $delete_attr_query->add('inventory_id', $this->getId());
        $delete_attr_query->doDelete();

        $delete_feature_query = new Db_Query('inventory_feature');
        $delete_feature_query->add('inventory_id', $this->getId());
        $delete_feature_query->doDelete();

        $delete_clapp_query = new Db_Query('inventory_clapp');
        $delete_clapp_query->add('inventory_id', $this->getId());
        $delete_clapp_query->doDelete();

        $this->updateTable('delete');

        return parent::_delete();
    }

    // Upload Image
    public function _uploadImage($url)
    {
        // Get File Path For Image
        $filepath = UploadHelper::getUploadDirectory(UploadConst::UPLOAD_TYPE_IMAGE, array(
            $this->dealer_id,
            $this->id
        ));

        // Create Directory, If It Doesn't Already Exist
        UploadHelper::createDirectory($filepath, 0775);
        $tempname = UploadHelper::hash(time()) . base_convert(rand(1, getrandmax()), 10, 36);
        $filename = $filepath . DS . $tempname . ".tmp";
        $extension = "";

        // Rename URL to Filename
        try {
            // Save the File
            rename($url, $filename);
        } catch (Exception $e) {
            Log::error("Could not save file to '{$filename}'. Reason: " . $e->getMessage());
            return 'save-failed';
        }

        // Image No Longer Existed?
        if (!file_exists($filename)) {
            Log::error("Uploaded file '{$filename}' not found.");
            return 'upload-failed';
        }

        // No Data
        $imageinfo = getimagesize($filename);
        if ($imageinfo === false) {
            Log::error("Uploaded file '{$filename}' is empty.");
            return 'upload-failed';
        }

        // File Exists?
        if (file_exists($filename)) {
            Log::debug("Uploaded file '{$filename}' was found.");

            // Get Extension
            $imageinfo = getimagesize($filename);
            $mimetype = $imageinfo['mime'];

            // No Extension
            $extension = "";
            switch ($mimetype) {
                case "image/gif":
                    $extension = "gif";
                    break;
                case "image/jpeg":
                    $extension = "jpg";
                    break;
                case "image/png":
                    $extension = "png";
                    break;
                default:
                    $extension = "";
                    break;
            }

            // Valid Extension? Finish Saving File
            if ($extension != "") {
                // Get Filename
                $inventoryFilenameTitle = $this->title . "_clapp1" . ".{$extension}";

                $newfilename = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array(
                    '_',
                    '.',
                    ''
                ), $inventoryFilenameTitle);

                // Resize Image
                ImageHelper::resize($filename, 800, 800, true, $filename);

                // Upload File to S3
                $path = UploadHelper::getS3Path($newfilename, array(
                    $this->dealer_id,
                    $this->id
                ));

                UploadHelper::putImageToS3($filename, $path, $mimetype);

                // Delete Old File
                unlink($filename);

                // New Filename Exists?
                Log::info("File '{$filename}' renamed to final '{$path}'.");
                $filename = '/' . $path;
            } else {
                Log::error("Uploaded data is not of expected mime-type. Required: image/png, image/jpeg, image/gif. Given: {$mimetype}.");
                return 'image-invalid';
            }
        }

        // Return Filename
        return $filename;
    }

    public function getIdentifier()
    {
        $inventoryIdentifier = CompactHelper::shorten($this->inventory_id);

        return $inventoryIdentifier;
    }

    public function getDealerIdentifier()
    {
        return CompactHelper::shorten($this->dealer_id);
    }

    public function getDealerLocationIdentifier()
    {
        return CompactHelper::shorten($this->dealer_location_id);
    }

    static function inventoryByStockAndDealer($stock, $dealerId)
    {
        if (!!$stock && !!$dealerId) {
            return DB::table(self::getTable())->where([['stock', $stock], ['dealer_id', $dealerId]])->get();
        } else {
            return false;
        }
    }

    static function inventoryByDealer($dealerId)
    {
        return response("You should use Dealer model instead.", '500');
    }

    static function inventoryByLocation($locationId, $offset = 0, $limit = false, $active = true)
    {
        return response("You should use Location model instead.", '500');
    }

    static function inventoryCountForStockAndDealer($stock, $dealerId)
    {
        if (!!$stock && !!$dealerId) {
            return DB::table(self::getTable())->where([['stock', $stock], ['dealer_id', $dealerId]])->count()->get();
        }
    }

    public function getLatitude()
    {
        if (!!$this->geolocation) {
            $data = GeospatialHelper::FromWKB($this->geolocation);

            return $data['lat'];
        } else {
            return null;
        }
    }

    public function setLatitude($latitude)
    {
        $longitude = $this->getLongitude();
        if (empty($longitude) || is_null($longitude)) {
            $longitude = 0;
        }
        $this->geolocation = GeospatialHelper::ToWKB($latitude, $longitude);
    }

    public function getLongitude()
    {
        $geolocation = $this->getData('geolocation');
        if ($geolocation) {
            $data = GeospatialHelper::FromWKB($geolocation);

            return $data['lon'];
        } else {
            return null;
        }
    }

    public function getImages()
    {
        //TODO renew function for laravel
        $query = new Db_Query('inventory_image');

        $query->add('inventory_id', $this->id);
        $query->order('IFNULL(position, 99)', 'ASC');
        $query->order('inventory_image.image_id', 'ASC');

        $inventoryImageData = $query->doSelect();

        $imageData = array();
        while ($row = $inventoryImageData->fetch(PDO::FETCH_OBJ)) {
            $imageModel = new Image();
            $imageModel->load($row->image_id);

            // Handle Noverlay Special
            $noverlay = $imageModel->getData('filename_noverlay');
            $imageData[] = array(
                'identifier' => CompactHelper::shorten($imageModel->id),
                'url' => ImageHelper::getImageUrl($imageModel->filename),
                'noverlay' => (!empty($noverlay) ? ImageHelper::getImageUrl($noverlay) : ''),
                'position' => $row->position
            );
        }

        return $imageData;

    }

    public function getFiles()
    {
        //TODO renew function for laravel

        $query = new Db_Query('inventory_file');

        $query->add('inventory_id', $this->id);
        $query->order('position', 'asc');

        $inventoryFileData = $query->doSelect();

        $fileData = array();
        while ($row = $inventoryFileData->fetch(PDO::FETCH_OBJ)) {
            $fileData[] = array(
                'file_id' => $row->file_id,
                'inventory_id' => $row->inventory_id,
                'position' => $row->position
            );
        }

        return $fileData;

    }

    public function setLongitude($longitude)
    {
        //TODO renew function for laravel
        $latitude = $this->getLatitude();
        if (empty($latitude) || is_null($latitude)) {
            $latitude = 0;
        }
        $this->geolocation = GeospatialHelper::ToWKB($latitude, $longitude);
    }

    public function duplicate()
    {

        $newModel = clone($this);

        $newModel->_origData = null;

        unset($newModel->_data['inventory_id']);
        $newModel->_data['stock'] = $newModel->_data['stock'] . '-' . time();
        unset($newModel->_data['created_at']);
        unset($newModel->_data['updated_at']);
        unset($newModel->_data['vin']);

        return $newModel;

    }
}
