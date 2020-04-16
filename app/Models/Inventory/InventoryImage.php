<?php

namespace App\Models\Inventory;

use App\Models\Interactions\DealerUpload;
use App\Models\User\Dealer;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;

class InventoryImage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_image';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image_id',
        'inventory_id',
        'position',
        'is_secondary',
        'was_manually_added'
    ];

    public function load($image_id, $inventory_id) {
        try {
            $data = InventoryImage::where([['image_id', $image_id],['inventory_id', $inventory_id]])->get();

            foreach ($data as $row) {
                foreach($row as $key => $value) {
                    $this->$key = $value;
                }
            }
        } catch(Exception $e) {
            Logger::getLogger('database')->error($e->getMessage(), $this);

            throw new Exception("SELECT failed for '{$this->getTableName()} 'image_id:{$image_id}', 'inventory_id:{$inventory_id}", Resource_Errors_Database::DATABASE_SELECT_FAILED, $e);
        }

        $this->_origData = $this->_data;

        if($data->rowCount() > 0) {
            return $this;
        } else {
            return false;
        }
    }

    protected function _insert() {
        $now = new DateTime();
        $this->setData('created_at', $now->format('Y-m-d H:i:s'));

        $statement = new Db_Query_Statement(Db_Manager::getConnection());

        $fields       = array();
        $values       = array();
        $placeholders = array();
        foreach($this->_data as $name => $value) {
            if(in_array($name, $this->_allowedFields)) {
                $fields[]       = $statement->getConnection()->quoteIdentifier($name);
                $values[]       = $value;
                $placeholders[] = '?';
            }
        }

        try {
            $queryString = "INSERT INTO `{$this->_tableName}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ");";

            $statement->setString($queryString);
            $statement->setParams($values);

            $sql = $statement->getString();

            Logger::getLogger('database')->debug($statement);
            $result = $statement->bindAndExecute();
            $count  = $result->rowCount();

            $id = $statement->getConnection()->lastInsertId();
            $this->setData($this->getIdFieldName(), $id);
        } catch(Exception $e) {
            Logger::getLogger('database')->error($e, $this);

            throw new Exception("INSERT failed for '{$this->getTableName()}", Resource_Errors_Database::DATABASE_INSERT_FAILED, $e);
        }
    }

    protected function _update() {
        $now = new DateTime();
        $this->setData('updated_at', $now->format('Y-m-d H:i:s'));

        $statement = new Db_Query_Statement(Db_Manager::getConnection());

        $queryString = "UPDATE `{$this->_tableName}` SET";

        foreach($this->_data as $name => $value) {
            if(in_array($name, $this->_allowedFields)) {
                if($value === null && (isset($this->_origData[ $name ]) && $this->_origData[ $name ] == $value)) {
                    continue;
                }
                $identifier = $statement->getConnection()->quoteIdentifier($name);

                $queryString .= "{$identifier} = '{$value}', ";
            }
        }

        if(strpos($queryString, '=') > 0) {
            $queryString = rtrim($queryString, ', ');
            $queryString .= " WHERE image_id = {$this->getData('image_id')} AND inventory_id = {$this->getData('inventory_id')}";

            try {
                $statement->setString($queryString);
                Logger::getLogger('database')->debug($statement);

                $result = $statement->bindAndExecute();
                $count  = $result->rowCount();
            } catch(Exception $e) {
                Logger::getLogger('database')->error($e, $this);

                throw new Exception("UPDATE failed for '{$this->getTableName()} 'image_id:{$this->getData('image_id')}', 'inventory_id:{$this->getData('inventory_id')}'", Resource_Errors_Database::DATABASE_UPDATE_FAILED, $e);
            }
        } else {
            $count = 0;
        }

        return $count;
    }

    protected function _delete() {
        $query = new Db_Query($this->getTableName());

        try {
            $query->add('image_id', $this->getData('image_id'));
            $query->add('inventory_id', $this->getData('inventory_id'));
            Logger::getLogger('database')->debug($query);

            $query->doDelete();

            $this->_isDeleted = true;
        } catch(Exception $e) {
            Logger::getLogger('database')->error($e, $this);

            throw new Exception("DELETE failed for '{$this->getTableName()} 'image_id:{$this->getData('image_id')}', 'inventory_id:{$this->getData('inventory_id')}'", Resource_Errors_Database::DATABASE_DELETE_FAILED);
        }

        return true;
    }

    static function getDefaultImage($inventory_id) {
        $query = new Db_Query('inventory_image');
        $query->add('inventory_id', $inventory_id);
        $query->order('position');
        $imageData = $query->doSelect();

        $row = $imageData->fetch(PDO::FETCH_OBJ);

        if($row) {
            $imageModel = new Model_Image();
            $imageModel->load($row->image_id);

            if($imageModel->hasData()) {
                return $imageModel;
            }
        } else {
            return null;
        }
    }

    static function getNextInventoryImagePosition($inventory_id) {
        $query = new Db_Query('inventory_image');
        $query->add('inventory_id', $inventory_id);
        $query->order('position', 'desc');
        $imageData = $query->doSelect();

        $row = $imageData->fetch(PDO::FETCH_OBJ);

        if($row) {
            return intval($row->position + 1);
        } else {
            return 1;
        }
    }
}
