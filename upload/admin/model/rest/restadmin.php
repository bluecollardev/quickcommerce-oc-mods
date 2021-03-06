<?php
class ModelRestRestAdmin extends Model {

    public function addAttributeGroup($data) {


        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group SET sort_order = '" . (int)$data['sort_order'] . "'");

        $attribute_group_id = $this->db->getLastId();

        foreach ($data['attribute_group_description'] as $attribute_group_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$attribute_group_id . "', language_id = '" . (int)$attribute_group_description['language_id'] . "', name = '" . $this->db->escape($attribute_group_description['name']) . "'");
        }

        return $attribute_group_id;
    }


    public function editAttributeGroup($attribute_group_id, $data) {


        $this->db->query("UPDATE " . DB_PREFIX . "attribute_group SET sort_order = '" . (int)$data['sort_order'] . "' WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");

        foreach ($data['attribute_group_description'] as $attribute_group_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$attribute_group_id . "', language_id = '" . (int)$attribute_group_description['language_id'] . "', name = '" . $this->db->escape($attribute_group_description['name']) . "'");
        }
    }

    public function deleteAttributeGroup($attribute_group_id) {


        $this->db->query("DELETE FROM " . DB_PREFIX . "attribute_group WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");

    }

    public function getAttributeGroups($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "attribute_group ag
                LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id)";
        if (!empty($data['language_id'])) {
            $sql.= " WHERE agd.language_id ='" . (int)$data['language_id'] . "'";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalAttributesByAttributeGroupId($attribute_group_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "attribute WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");

        return $query->row['total'];
    }

    public function addAttribute($data) {


        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

        $attribute_id = $this->db->getLastId();

        foreach ($data['attribute_description'] as $attribute_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$attribute_description['language_id'] . "', name = '" . $this->db->escape($attribute_description['name']) . "'");
        }
        return $attribute_id;
    }

    public function editAttribute($attribute_id, $data) {


        $this->db->query("UPDATE " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE attribute_id = '" . (int)$attribute_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "'");

        foreach ($data['attribute_description'] as $attribute_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$attribute_description['language_id'] . "', name = '" . $this->db->escape($attribute_description['name']) . "'");
        }


    }

    public function deleteAttribute($attribute_id) {


        $this->db->query("DELETE FROM " . DB_PREFIX . "attribute WHERE attribute_id = '" . (int)$attribute_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "'");


    }

    public function getAttributes($data = array()) {
        $sql = "SELECT *";
           $sql.="
                FROM " . DB_PREFIX . "attribute a
                INNER JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id)
                ";


        if (!empty($data['language_id'])) {
            $sql.= "WHERE ad.language_id = '" . (int)$data['language_id'] . "'";
        }
        if (!empty($data['filter_name'])) {
            $sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_attribute_group_id'])) {
            $sql .= " AND a.attribute_group_id = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
        }

        $sort_data = array(
            'ad.name',
            'attribute_group_id',
            'a.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY attribute_group_id, ad.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalProductsByAttributeId($attribute_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByOptionId($option_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

        return $query->row['total'];
    }

    public function addOption($data) {

        $this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

        $option_id = $this->db->getLastId();

        foreach ($data['option_description'] as $option_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$option_description['language_id'] . "', name = '" . $this->db->escape($option_description['name']) . "'");
        }

        if (isset($data['option_value'])) {
            foreach ($data['option_value'] as $option_value) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'");

                $option_value_id = $this->db->getLastId();

                foreach ($option_value['option_value_description'] as $option_value_description) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$option_value_description['language_id'] . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($option_value_description['name']) . "'");
                }
            }
        }

        return $option_id;
    }

    public function editOption($option_id, $data) {

        $this->db->query("UPDATE `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE option_id = '" . (int)$option_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "'");

        foreach ($data['option_description'] as $option_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$option_description['language_id'] . "', name = '" . $this->db->escape($option_description['name']) . "'");
        }

        if (isset($data['option_value'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "'");

            foreach ($data['option_value'] as $option_value) {
                if ($option_value['option_value_id']) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_value_id = '" . (int)$option_value['option_value_id'] . "', option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'");
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$option_value['sort_order'] . "'");
                }

                $option_value_id = $this->db->getLastId();

                foreach ($option_value['option_value_description'] as $option_value_description) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$option_value_description['language_id'] . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($option_value_description['name']) . "'");
                }
            }

        }

    }

    public function deleteOption($option_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option` WHERE option_id = '" . (int)$option_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "'");
    }

    public function getOptions($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND od.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'od.name',
            'o.type',
            'o.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY od.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getOptionValueDescriptions($option_id) {
        $option_value_data = array();

        $option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "' ORDER BY sort_order");

        foreach ($option_value_query->rows as $option_value) {
            $option_value_description_data = array();

            $option_value_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE option_value_id = '" . (int)$option_value['option_value_id'] . "'");

            foreach ($option_value_description_query->rows as $option_value_description) {
                $option_value_description_data[$option_value_description['language_id']] = array(
                    'name' => $option_value_description['name'],
                    'language_id'=> $option_value_description['language_id']
                );
            }

            $option_value_data[] = array(
                'option_value_id'          => $option_value['option_value_id'],
                'option_value_description' => $option_value_description_data,
                'image'                    => $option_value['image'],
                'sort_order'               => $option_value['sort_order'],
            );
        }

        return $option_value_data;
    }

    public function getCustomers($data = array()) {
        $sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
            $implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
        }

        if (!empty($data['filter_customer_group_id'])) {
            $implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
        }

        if (!empty($data['filter_ip'])) {
            $implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
            $implode[] = "c.approved = '" . (int)$data['filter_approved'] . "'";
        }

        if (!empty($data['filter_date_added_on'])) {
            $implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added_on']) . "')";
        }

        if (!empty($data['filter_date_added_to']) && !empty($data['filter_date_added_from'])) {

            $implode[] = " c.date_added BETWEEN STR_TO_DATE('" . $this->db->escape($data['filter_date_added_from']) . "','%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('" . $this->db->escape($data['filter_date_added_to']) . "','%Y-%m-%d %H:%i:%s')";

        } elseif (!empty($data['filter_date_added_from'])) {

            $implode[] = " c.date_added >= STR_TO_DATE('" . $this->db->escape($data['filter_date_added_from']) . "','%Y-%m-%d %H:%i:%s')";
        }

        if ($implode) {
            $sql .= " AND " . implode(" AND ", $implode);
        }

        $sort_data = array(
            'name',
            'c.email',
            'customer_group',
            'c.status',
            'c.approved',
            'c.ip',
            'c.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function deleteCustomer($customer_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");
    }

    public function addCustomerGroup($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_group SET approval = '" . (int)$data['approval'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

        $customer_group_id = $this->db->getLastId();

        foreach ($data['customer_group_description'] as $customer_group_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "customer_group_description SET customer_group_id = '" . (int)$customer_group_id . "', language_id = '" . (int)$customer_group_description['language_id'] . "', name = '" . $this->db->escape($customer_group_description['name']) . "', description = '" . $this->db->escape($customer_group_description['description']) . "'");
        }

        return $customer_group_id;
    }

    public function editCustomerGroup($customer_group_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer_group SET approval = '" . (int)$data['approval'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE customer_group_id = '" . (int)$customer_group_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = '" . (int)$customer_group_id . "'");

        foreach ($data['customer_group_description'] as $customer_group_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "customer_group_description SET customer_group_id = '" . (int)$customer_group_id . "', language_id = '" . (int)$customer_group_description['language_id'] . "', name = '" . $this->db->escape($customer_group_description['name']) . "', description = '" . $this->db->escape($customer_group_description['description']) . "'");
        }
    }

    public function deleteCustomerGroup($customer_group_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_group WHERE customer_group_id = '" . (int)$customer_group_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = '" . (int)$customer_group_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE customer_group_id = '" . (int)$customer_group_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE customer_group_id = '" . (int)$customer_group_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE customer_group_id = '" . (int)$customer_group_id . "'");
    }

    public function getCustomerGroups($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id)";

        if (!empty($data['language_id'])) {
            $sql.= "WHERE cgd.language_id =  '" . (int)$data['language_id'] . "'";
        }

        $sort_data = array(
            'cgd.name',
            'cg.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY cgd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalCustomersByCustomerGroupId($customer_group_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE customer_group_id = '" . (int)$customer_group_id . "'");

        return $query->row['total'];
    }

    public function getTotalStoresByCustomerGroupId($customer_group_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "setting WHERE `key` = 'config_customer_group_id' AND `value` = '" . (int)$customer_group_id . "' AND store_id != '0'");

        return $query->row['total'];
    }

    public function getCustomer($customer_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row;
    }

    public function editCustomer($customer_id, $data) {
        if (!isset($data['custom_field'])) {
            $data['custom_field'] = array();
        }

        $this->db->query("UPDATE " . DB_PREFIX . "customer SET customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? serialize($data['custom_field']) : '') . "', newsletter = '" . (int)$data['newsletter'] . "', status = '" . (int)$data['status'] . "', approved = '" . (int)$data['approved'] . "', safe = '" . (int)$data['safe'] . "' WHERE customer_id = '" . (int)$customer_id . "'");

        if (isset($data['password'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE customer_id = '" . (int)$customer_id . "'");
        }

        if (isset($data['address'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");
            foreach ($data['address'] as $address) {
                if (!isset($address['custom_field'])) {
                    $address['custom_field'] = array();
                }

                $this->db->query("INSERT INTO " . DB_PREFIX . "address SET address_id = '" . (int)$address['address_id'] . "', customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "', custom_field = '" . $this->db->escape(isset($address['custom_field']) ? serialize($address['custom_field']) : '') . "'");

                if (isset($address['default'])) {
                    $address_id = $this->db->getLastId();

                    $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
                }
            }
        }
    }

    public function addCustomer($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer SET customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? serialize($data['custom_field']) : '') . "', newsletter = '" . (int)$data['newsletter'] . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', status = '" . (int)$data['status'] . "', approved = '" . (int)$data['approved'] . "', safe = '" . (int)$data['safe'] . "', date_added = NOW()");

        $customer_id = $this->db->getLastId();

        if (isset($data['address'])) {
            foreach ($data['address'] as $address) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "', custom_field = '" . $this->db->escape(isset($address['custom_field']) ? serialize($address['custom_field']) : '') . "'");

                if (isset($address['default'])) {
                    $address_id = $this->db->getLastId();

                    $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
                }
            }
        }

        return $customer_id;
    }

    public function getCustomFields($data = array()) {
        if (empty($data['filter_customer_group_id'])) {
            $sql = "SELECT * FROM `" . DB_PREFIX . "custom_field` cf LEFT JOIN " . DB_PREFIX . "custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id)";
            if (!empty($data['language_id'])) {
                $sql.= "WHERE cfd.language_id =  '" . (int)$data['language_id'] . "'";
            }
        } else {
            $sql = "SELECT * FROM " . DB_PREFIX . "custom_field_customer_group cfcg LEFT JOIN `" . DB_PREFIX . "custom_field` cf ON (cfcg.custom_field_id = cf.custom_field_id) LEFT JOIN " . DB_PREFIX . "custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id)";
            if (!empty($data['language_id'])) {
                $sql.= "WHERE cfd.language_id =  '" . (int)$data['language_id'] . "'";
            }
        }

        if (!empty($data['filter_name'])) {
            $sql .= " AND cfd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_customer_group_id'])) {
            $sql .= " AND cfcg.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
        }

        $sort_data = array(
            'cfd.name',
            'cf.type',
            'cf.location',
            'cf.status',
            'cf.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY cfd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getAddresses($customer_id) {
        $address_data = array();

        $query = $this->db->query("SELECT address_id FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");

        foreach ($query->rows as $result) {
            $address_info = $this->getAddress($result['address_id']);

            if ($address_info) {
                $address_data[$result['address_id']] = $address_info;
            }
        }

        return $address_data;
    }


    public function getAddress($address_id) {
        $address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "'");

        if ($address_query->num_rows) {
            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");

            if ($country_query->num_rows) {
                $country = $country_query->row['name'];
                $iso_code_2 = $country_query->row['iso_code_2'];
                $iso_code_3 = $country_query->row['iso_code_3'];
                $address_format = $country_query->row['address_format'];
            } else {
                $country = '';
                $iso_code_2 = '';
                $iso_code_3 = '';
                $address_format = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");

            if ($zone_query->num_rows) {
                $zone = $zone_query->row['name'];
                $zone_code = $zone_query->row['code'];
            } else {
                $zone = '';
                $zone_code = '';
            }

            return array(
                'address_id'     => $address_query->row['address_id'],
                'customer_id'    => $address_query->row['customer_id'],
                'firstname'      => $address_query->row['firstname'],
                'lastname'       => $address_query->row['lastname'],
                'company'        => $address_query->row['company'],
                'address_1'      => $address_query->row['address_1'],
                'address_2'      => $address_query->row['address_2'],
                'postcode'       => $address_query->row['postcode'],
                'city'           => $address_query->row['city'],
                'zone_id'        => $address_query->row['zone_id'],
                'zone'           => $zone,
                'zone_code'      => $zone_code,
                'country_id'     => $address_query->row['country_id'],
                'country'        => $country,
                'iso_code_2'     => $iso_code_2,
                'iso_code_3'     => $iso_code_3,
                'address_format' => $address_format,
                'custom_field'   => unserialize($address_query->row['custom_field'])
            );
        }
    }


    public function getTaxClasses($data = array()) {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "tax_class";

            $sql .= " ORDER BY title";

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        } else {
            $tax_class_data = $this->cache->get('tax_class');

            if (!$tax_class_data) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tax_class");

                $tax_class_data = $query->rows;

                $this->cache->set('tax_class', $tax_class_data);
            }

            return $tax_class_data;
        }
    }
    public function getTaxRates($data = array()) {
        $sql = "SELECT tr.tax_rate_id, tr.name AS name, tr.rate, tr.type, gz.name AS geo_zone, tr.date_added, tr.date_modified FROM " . DB_PREFIX . "tax_rate tr LEFT JOIN " . DB_PREFIX . "geo_zone gz ON (tr.geo_zone_id = gz.geo_zone_id)";

        $sort_data = array(
            'tr.name',
            'tr.rate',
            'tr.type',
            'gz.name',
            'tr.date_added',
            'tr.date_modified'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY tr.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getWeightClasses() {
        $sql = "SELECT * FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id)";
        $sql .= " ORDER BY title";
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getStockStatuses($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "stock_status";

        $sql .= " ORDER BY name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getLengthClasses() {
            $sql = "SELECT * FROM " . DB_PREFIX . "length_class lc LEFT JOIN " . DB_PREFIX . "length_class_description lcd ON (lc.length_class_id = lcd.length_class_id)";

            $sql .= " ORDER BY title";

            $query = $this->db->query($sql);

            return $query->rows;
    }

    public function getStores($data = array()) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store ORDER BY url");

        $store_data = $query->rows;

        return $store_data;
    }

    public function getRecurrings($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "recurring` r LEFT JOIN " . DB_PREFIX . "recurring_description rd ON (r.recurring_id = rd.recurring_id)";
        $sql .= " ORDER BY rd.name";
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getOrderStatuses() {

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status ORDER BY name");

        return $query->rows;
    }

    public function getCategory($category_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

        return $query->rows;
    }

    public function getCategories($parent_id = 0) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY c.sort_order, LCASE(cd.name)");

        return $query->rows;
    }

    public function addCategory($data) {

        $this->db->query("INSERT INTO " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

        $category_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
        }

        foreach ($data['category_description'] as $category_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$category_description['language_id'] . "', name = '" . $this->db->escape($category_description['name']) . "', description = '" . $this->db->escape($category_description['description']) . "', meta_title = '" . $this->db->escape($category_description['meta_title']) . "', meta_description = '" . $this->db->escape($category_description['meta_description']) . "', meta_keyword = '" . $this->db->escape($category_description['meta_keyword']) . "'");
        }

        // MySQL Hierarchical Data Closure Table Pattern
        $level = 0;

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

        foreach ($query->rows as $result) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

            $level++;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

        if (isset($data['category_filter'])) {
            foreach ($data['category_filter'] as $filter_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
            }
        }

        if (isset($data['category_store'])) {
            foreach ($data['category_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        // Set which layout to use with this category
        if (isset($data['category_layout'])) {
            foreach ($data['category_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }

        if (isset($data['keyword'])) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        $this->cache->delete('category');


        return $category_id;
    }

    public function editCategory($category_id, $data) {

        $this->db->query("UPDATE " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");

        foreach ($data['category_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        // MySQL Hierarchical Data Closure Table Pattern
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

        if ($query->rows) {
            foreach ($query->rows as $category_path) {
                // Delete the path below the current one
                $this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

                $path = array();

                // Get the nodes new parents
                $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

                foreach ($query->rows as $result) {
                    $path[] = $result['path_id'];
                }

                // Get whats left of the nodes current path
                $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

                foreach ($query->rows as $result) {
                    $path[] = $result['path_id'];
                }

                // Combine the paths with a new level
                $level = 0;

                foreach ($path as $path_id) {
                    $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

                    $level++;
                }
            }
        } else {
            // Delete the path below the current one
            $this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_id . "'");

            // Fix for records with no paths
            $level = 0;

            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

            foreach ($query->rows as $result) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

                $level++;
            }

            $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['category_filter'])) {
            foreach ($data['category_filter'] as $filter_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['category_store'])) {
            foreach ($data['category_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['category_layout'])) {
            foreach ($data['category_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "'");

        if ($data['keyword']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        $this->cache->delete('category');
    }

    public function deleteCategory($category_id) {

        $this->db->query("DELETE FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_path WHERE path_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $this->deleteCategory($result['category_id']);
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "'");

        $this->cache->delete('category');

    }

    public function getProductCategories($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
        $ids = array();

        foreach ($query->rows as $result) {
            $ids[] = $result['category_id'];
        }

        if(count($ids)){
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
            LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)
            WHERE c.category_id IN (" . implode(',', $ids) . ") AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY c.sort_order, LCASE(cd.name)");

            return $query->rows;
        }

        return array();
    }

    public function getProductAttributes($product_id) {

        $query = $this->db->query("
        SELECT a.attribute_id as attribute_id, ad.name, pa.text , a.attribute_group_id, ad.language_id
        FROM " . DB_PREFIX . "product_attribute pa
        LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id)
        LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id)
        WHERE pa.product_id = '" . (int)$product_id . "'
        ORDER BY a.sort_order, ad.name");

        $attributes = array();

        foreach ($query->rows as $result) {
            $languageId = isset($result['language_id']) ? $result['language_id'] : (int)$this->config->get('config_language_id');
            $attributes['attributes'][$result['attribute_id']][$languageId] = array(
                'attribute_id'    => $result['attribute_id'],
                'name'            => $result['name'],
                'text'            => $result['text'],
                'attribute_group_id' => $result['attribute_group_id'],
                'language_id'      => $languageId
            );
        }

        return $attributes;

    }

    public function getChecksum() {
        $query = $this->db->query("CHECKSUM TABLE " . DB_PREFIX . "product, "
            . DB_PREFIX . "category,"
            . DB_PREFIX . "product_to_category,"
            . DB_PREFIX . "product_description"

        );
        return $query->rows;
    }

    public function getTotalProductsByManufacturerId($manufacturer_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        return $query->row['total'];
    }


    public function editProductById($product_id, $data) {

        $this->db->query("UPDATE " . DB_PREFIX . "product SET
						model = '" . $this->db->escape($data['model']) . "',
						sku = '" . $this->db->escape($data['sku']) . "',
						upc = '" . $this->db->escape($data['upc']) . "',
						ean = '" . $this->db->escape($data['ean']) . "',
						jan = '" . $this->db->escape($data['jan']) . "',
						isbn = '" . $this->db->escape($data['isbn']) . "',
						mpn = '" . $this->db->escape($data['mpn']) . "',
						location = '" . $this->db->escape($data['location']) . "',
						quantity = '" . (int)$data['quantity'] . "',
						minimum = '" . (int)$data['minimum'] . "',
						subtract = '" . (int)$data['subtract'] . "',
						stock_status_id = '" . (int)$data['stock_status_id'] . "',
						date_available = '" . $this->db->escape($data['date_available']) . "',
						manufacturer_id = '" . (int)$data['manufacturer_id'] . "',
						shipping = '" . (int)$data['shipping'] . "',
						price = '" . (float)$data['price'] . "',
						points = '" . (int)$data['points'] . "',
						weight = '" . (float)$data['weight'] . "',
						weight_class_id = '" . (int)$data['weight_class_id'] . "',
						length = '" . (float)$data['length'] . "',
						width = '" . (float)$data['width'] . "',
						height = '" . (float)$data['height'] . "',
						length_class_id = '" . (int)$data['length_class_id'] . "',
						status = '" . (int)$data['status'] . "',
						tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "',
						sort_order = '" . (int)$data['sort_order'] . "',
						image = '" . $this->db->escape(html_entity_decode($data["image"], ENT_QUOTES, 'UTF-8')) . "',
						date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");

        if(isset($data['product_description']) && !empty($data['product_description'])){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

            foreach ($data['product_description'] as $product_description) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$product_description["language_id"] . "', name = '" . $this->db->escape($product_description['name']) . "', meta_title = '" . $this->db->escape($product_description['meta_title']) . "', meta_keyword = '" . $this->db->escape($product_description['meta_keyword']) . "', meta_description = '" . $this->db->escape($product_description['meta_description']) . "', description = '" . $this->db->escape($product_description['description']) . "'");
            }
        }

        if(isset($data['product_store']) && !empty($data['product_description'])){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int) $product_id . "'");

            if (isset($data['product_store'])) {
                foreach ($data['product_store'] as $store_id) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int) $product_id . "', store_id = '" . (int) $store_id . "'");
                }
            }
        }

        if(isset($data['product_category']) && !empty($data['product_category'])){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

            if (isset($data['product_category'])) {
                foreach ($data['product_category'] as $category_id) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
                }
            }
        }

        if (isset($data['product_option']) && isset($data['product_option_quantity_update']) && intval($data['product_option_quantity_update']) == 1) {
            foreach ($data['product_option'] as $product_option) {
                if (isset($product_option['product_option_value'])  && count($product_option['product_option_value']) > 0 ) {
                    foreach ($product_option['product_option_value'] as $product_option_value) {
                        $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = '" . (int)$product_option_value['quantity'] . "' WHERE product_id = '" . (int)$product_id . "' AND product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "'");
                    }
                }
            }
        } elseif (isset($data['product_option'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");

            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "',
                                    option_id = '" . (int)$product_option['option_id'] . "',
                                    required = '" . (int)$product_option['required'] . "'");

                    $product_option_id = $this->db->getLastId();

                    foreach ($product_option['product_option_value'] as $product_option_value) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "',
                        product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "',
                        option_value_id = '" . (int)$product_option_value['option_value_id'] . "',
                        quantity = '" . (int)$product_option_value['quantity'] . "',
                        subtract = '" . (int)$product_option_value['subtract'] . "',
                        price = '" . (float)$product_option_value['price'] . "',
                        price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "',
                        points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
                    }

                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['option_value']) . "', required = '" . (int)$product_option['required'] . "'");
                }
            }
        }

        if (!empty($data['product_attribute'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

                    foreach ($product_attribute['product_attribute_description'] as $product_attribute_description) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$product_attribute_description['language_id'] . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        if (isset($data['product_discount'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
            foreach ($data['product_discount'] as $product_discount) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
            }
        }

        if (isset($data['product_special'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
            foreach ($data['product_special'] as $product_special) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
            }
        }

        $this->cache->delete('product');
    }

    public function addProduct($data) {

        $this->db->query("INSERT INTO " . DB_PREFIX . "product
                        SET model = '" . $this->db->escape($data['model']) . "',
						parent_id = '" . $this->db->escape($data['parent_id']) . "',
						qbname = '" . $this->db->escape($data['qbname']) . "',
						sku = '" . $this->db->escape($data['sku']) . "',
                        upc = '" . $this->db->escape($data['upc']) . "',
                        ean = '" . $this->db->escape($data['ean']) . "',
                        jan = '" . $this->db->escape($data['jan']) . "',
                        isbn = '" . $this->db->escape($data['isbn']) . "',
                        mpn = '" . $this->db->escape($data['mpn']) . "',
                        location = '" . $this->db->escape($data['location']) . "',
                        quantity = '" . (int)$data['quantity'] . "',
                        minimum = '" . (int)$data['minimum'] . "',
                        subtract = '" . (int)$data['subtract'] . "',
                        stock_status_id = '" . (int)$data['stock_status_id'] . "',
                        date_available = '" . $this->db->escape($data['date_available']) . "',
                        manufacturer_id = '" . (int)$data['manufacturer_id'] . "',
                        shipping = '" . (int)$data['shipping'] . "',
                        price = '" . (float)$data['price'] . "',
                        points = '" . (int)$data['points'] . "',
                        weight = '" . (float)$data['weight'] . "',
                        weight_class_id = '" . (int)$data['weight_class_id'] . "',
                        length = '" . (float)$data['length'] . "',
                        width = '" . (float)$data['width'] . "',
                        height = '" . (float)$data['height'] . "',
                        length_class_id = '" . (int)$data['length_class_id'] . "',
                        status = '" . (int)$data['status'] . "',
                        tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "',
                        sort_order = '" . (int)$data['sort_order'] . "',
			            image = '" . $this->db->escape(html_entity_decode($data["image"], ENT_QUOTES, 'UTF-8')) . "',
                        date_added = NOW()");

        $product_id = $this->db->getLastId();


        //add other images to product
        if (isset($data['other_images']) && !empty($data['other_images']) && is_array($data['other_images'])) {
            foreach($data['other_images'] as $imagePath){
                $this->addProductImage($product_id, $imagePath);
            }
        }

        foreach ($data['product_description'] as $product_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$product_description["language_id"] . "', name = '" . $this->db->escape($product_description['name']) . "', meta_title = '" . $this->db->escape($product_description['meta_title']) . "', meta_keyword = '" . $this->db->escape($product_description['meta_keyword']) . "', meta_description = '" . $this->db->escape($product_description['meta_description']) . "', description = '" . $this->db->escape($product_description['description']) . "'");
        }

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "',
                                    option_id = '" . (int)$product_option['option_id'] . "',
                                    required = '" . (int)$product_option['required'] . "'");

                    $product_option_id = $this->db->getLastId();

                    foreach ($product_option['product_option_value'] as $product_option_value) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "',
                        product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "',
                        option_value_id = '" . (int)$product_option_value['option_value_id'] . "',
                        quantity = '" . (int)$product_option_value['quantity'] . "',
                        subtract = '" . (int)$product_option_value['subtract'] . "',
                        price = '" . (float)$product_option_value['price'] . "',
                        price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "',
                        points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
                    }

                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['option_value']) . "', required = '" . (int)$product_option['required'] . "'");
                }
            }
        }

        if (isset($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

                    foreach ($product_attribute['product_attribute_description'] as $product_attribute_description) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$product_attribute_description['language_id'] . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        if (isset($data['product_discount'])) {
            foreach ($data['product_discount'] as $product_discount) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
            }
        }

        if (isset($data['product_special'])) {
            foreach ($data['product_special'] as $product_special) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
            }
        }

        $this->cache->delete('product');

        return (int)$product_id;
    }

    public function deleteProduct($product_id) {

        $this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");

        $this->cache->delete('product');
    }


    public function setProductImage($product_id, $image) {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($image, ENT_QUOTES, 'UTF-8')) . "',  date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");
    }

    public function addProductImage($product_id, $image) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($image, ENT_QUOTES, 'UTF-8')) . "'");
    }

    public function getProductsData($data = array(), $customer) {

        if (!empty($data['customer_group'])) {
            $customer_group_id = (int)$data['customer_group'];
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        if (!empty($data['language'])) {
            $language = (int)$data['language'];
        } else {
            $language = (int)$this->config->get('config_language_id');
        }


        $sql = "SELECT p.product_id ";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
            } else {
                $sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
            }
            if (!empty($data['filter_filter'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id)
                                  LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
            } else {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
            }
        } else {
            $sql .= " FROM " . DB_PREFIX . "product p";
        }

        $statusSql = "";
        if (!empty($data['status'])) {
            $statusSql = " AND p.status = '".(int)$data['status']."'";
        }

        $storeSql = " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        $sql .= "   LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                            LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
                            WHERE true = true
                            ".$statusSql.$storeSql;


        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }
        }

        if (!empty($data['filter_name']) ) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                }
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            $sql .= ")";
        }

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'name'=>'pd.name',
            'model'=>'p.model',
            'quantity'=>'p.quantity',
            'price'=>'p.price',
            'sort_order'=>'p.sort_order',
            'date_added'=>'p.date_added'
        );

        $sortSql = "";
        if (isset($data['sort']) && in_array($data['sort'], array_keys($sort_data))) {
            if ($data['sort'] == 'name' || $data['sort'] == 'model') {
                $sortSql .= " ORDER BY LCASE(" . $sort_data[$data['sort']] . ")";
            } elseif ($data['sort'] == 'price') {
                $sortSql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
            } else {
                $sortSql .= " ORDER BY " . $sort_data[$data['sort']];
            }
        } else {
            $sortSql .= " ORDER BY p.sort_order";
        }

        if (isset($data['order']) && (strtolower($data['order']) == strtolower('DESC'))) {
            $sortSql .= " DESC, LCASE(pd.name) DESC";
        } else {
            $sortSql .= " ASC, LCASE(pd.name) ASC";
        }

        $sql.= $sortSql;

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['limit'] < 1) {
                $limit = 20;
            }else {
                $limit = (int)$data['limit'];
            }

            $offset = 0;
            if ($data['start'] < 0) {
                $offset = 0;
            }else{
                $offset = (int)$data['start'];
            }

            $sql .= " LIMIT " . $offset . "," . $limit;
        }

        $product_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $result['product_id'];
        }

        return $this->getProductsByIds(array_keys($product_data), $customer, $sortSql, $customer_group_id = null, $language = null);
    }

    public function getProductsByIds($product_ids, $customer, $sortSql = "ORDER BY p.product_id ASC", $customer_group_id=null, $language=null) {


        if (!empty($data['customer_group'])) {
            $customer_group_id = (int)$customer_group_id;
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        if (!empty($language)) {
            $language = (int)$language;
        } else {
            $language = $this->config->get('config_language_id');
        }

        if(count($product_ids) == 0){
            return false;
        }

        $query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer,
                                                (SELECT price
                                                    FROM " . DB_PREFIX . "product_discount pd2
                                                    WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "'
                                                    AND pd2.quantity = '1'
                                                    AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW())
                                                    AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW()))
                                                    ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount,
                                                (SELECT price
                                                        FROM " . DB_PREFIX . "product_special ps
                                                        WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "'
                                                        AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                                                        AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
                                                        ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
                                                (SELECT points
                                                    FROM " . DB_PREFIX . "product_reward pr
                                                        WHERE pr.product_id = p.product_id
                                                        AND customer_group_id = '" . (int)$customer_group_id . "') AS reward,
                                                (SELECT ss.name
                                                    FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id
                                                    AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status,
                                                (SELECT wcd.unit
                                                        FROM " . DB_PREFIX . "weight_class_description wcd
                                                        WHERE p.weight_class_id = wcd.weight_class_id
                                                        AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class,
                                                (SELECT lcd.unit
                                                        FROM " . DB_PREFIX . "length_class_description lcd
                                                        WHERE p.length_class_id = lcd.length_class_id
                                                        AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class,
                                                (SELECT AVG(rating) AS total
                                                        FROM " . DB_PREFIX . "review r1
                                                        WHERE r1.product_id = p.product_id
                                                        AND r1.status = '1'
                                                        GROUP BY r1.product_id) AS rating,
                                                (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2
                                                        WHERE r2.product_id = p.product_id AND r2.status = '1'
                                                        GROUP BY r2.product_id) AS reviews, p.sort_order
                                                FROM " . DB_PREFIX . "product p
                                                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                                                LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
                                                LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
                                                WHERE p.product_id IN (" . implode(',', $product_ids) . ")".$sortSql);
        $product_data = array();
        if ($query->num_rows) {
            foreach ($query->rows as $result) {
                $product_description = array();
                $product_description[$result['language_id']] = array(
                    'language_id'      => $result['language_id'],
                    'name'             => $result['name'],
                    'description'      => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                    'meta_description' => $result['meta_description'],
                    'meta_keyword'     => $result['meta_keyword'],
                    'meta_title'       => $result['meta_title'],
                    'tag'              => $result['tag']
                );

                if(!isset($product_data[$result['product_id']])){
                    $product_data[$result['product_id']] = array(
                        'product_id'       => $result['product_id'],
                        'model'            => $result['model'],
                        'sku'              => $result['sku'],
                        'product_description'=> $product_description,
                        'upc'              => $result['upc'],
                        'ean'              => $result['ean'],
                        'jan'              => $result['jan'],
                        'isbn'             => $result['isbn'],
                        'mpn'              => $result['mpn'],
                        'location'         => $result['location'],
                        'quantity'         => $result['quantity'],
                        'stock_status'     => $result['stock_status'],
                        'image'            => $result['image'],
                        'manufacturer_id'  => $result['manufacturer_id'],
                        'manufacturer'     => $result['manufacturer'],
                        'price'            => ($result['discount'] ? $result['discount'] : $result['price']),
                        'special'          => $result['special'],
                        'reward'           => $result['reward'],
                        'points'           => $result['points'],
                        'tax_class_id'     => $result['tax_class_id'],
                        'date_available'   => $result['date_available'],
                        'weight'           => $result['weight'],
                        'weight_class_id'  => $result['weight_class_id'],
                        'length'           => $result['length'],
                        'width'            => $result['width'],
                        'height'           => $result['height'],
                        'length_class_id'  => $result['length_class_id'],
                        'subtract'         => $result['subtract'],
                        'rating'           => round($result['rating']),
                        'reviews'          => $result['reviews'] ? $result['reviews'] : 0,
                        'minimum'          => $result['minimum'],
                        'sort_order'       => $result['sort_order'],
                        'status'           => $result['status'],
                        'date_added'       => $result['date_added'],
                        'date_modified'    => $result['date_modified'],
                        'viewed'           => $result['viewed'],
                        'weight_class'     => $result['weight_class'],
                        'length_class'     => $result['length_class']
                    );
                } else {
                    //$desc = $product_data[$result['product_id']]['product_description'];
                    //$product_data[$result['product_id']]['product_description'] = $desc;
                    $tmp = array();
                    foreach($product_description as $desc) {
                        $tmp = array(
                            'language_id'      => $desc['language_id'],
                            'name'             => $desc['name'],
                            'description'      => $desc['description'],
                            'meta_description' => $desc['meta_description'],
                            'meta_keyword'     => $desc['meta_keyword'],
                            'meta_title'       => $desc['meta_title'],
                            'tag'              => $desc['tag']
                        );
                    }
                    $product_data[$result['product_id']]['product_description'][$result['language_id']] = $tmp;
                }

            }
            return $product_data;
        } else {
            return false;
        }
    }
    public function getOrderStatusByName($status) {

        $query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order_status WHERE LCASE(name) = '" . $this->db->escape(utf8_strtolower($status)) . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->rows;
    }

    public function editProductQuantity($product_id, $data) {
        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                if (isset($product_option['product_option_value'])  && count($product_option['product_option_value']) > 0 ) {
                    foreach ($product_option['product_option_value'] as $product_option_value) {
                        $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = '" . (int)$product_option_value['quantity'] . "' WHERE product_id = '" . (int)$product_id . "' AND product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "'");
                    }
                }
            }
        }
        if (isset($data['quantity'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$data['quantity'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");
        }

        $this->cache->delete('product');
    }


    public function checkProductExists($product_id) {
        $query = $this->db->query("SELECT COUNT(DISTINCT product_id) AS total FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");

        if (isset($query->row['total'])) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function getAttribute($attribute_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE a.attribute_id = '" . (int)$attribute_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getAllOrders($start = 0, $limit = 20) {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 1;
        }

        $query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.order_status_id > '0' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getOrdersByUser($customer_id) {

        $query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$customer_id . "' AND o.order_status_id > '0' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC");

        return $query->rows;
    }

    public function getOrdersByFilter($data = array()) {
        $sql = "SELECT o.*, CONCAT(o.firstname, ' ', o.lastname) AS customer,
				            payment_country.iso_code_2 as pc_iso_code_2,
				            payment_country.iso_code_3 as pc_iso_code_3,
                            shipping_country.iso_code_2 as sc_iso_code_2,
				            shipping_country.iso_code_3 as sc_iso_code_3,
				            payment_zone.code as payment_zone_code,
				            shipping_zone.code as shipping_zone_code

				        FROM `" . DB_PREFIX . "order` o
				        LEFT JOIN `" . DB_PREFIX . "country` payment_country ON ( payment_country.country_id = o.payment_country_id)
				        LEFT JOIN `" . DB_PREFIX . "country` shipping_country ON ( shipping_country.country_id = o.shipping_country_id)
				        LEFT JOIN `" . DB_PREFIX . "zone` payment_zone ON ( payment_zone.zone_id = o.payment_zone_id)
				        LEFT JOIN `" . DB_PREFIX . "zone` shipping_zone ON ( shipping_zone.zone_id = o.shipping_zone_id)
				                    ";

        if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
            $sql .= " WHERE o.order_status_id IN ( ". $this->db->escape(rtrim($data['filter_order_status_id'],",")) . ")";
        } else {
            $sql .= " WHERE o.order_status_id > '0'";
        }

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_customer'])) {
            $sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }


        if (!empty($data['filter_date_added_to']) && !empty($data['filter_date_added_from'])) {

            $sql .= " AND o.date_added BETWEEN STR_TO_DATE('" . $this->db->escape($data['filter_date_added_from']) . "','%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('" . $this->db->escape($data['filter_date_added_to']) . "','%Y-%m-%d %H:%i:%s')";

        } elseif (!empty($data['filter_date_added_from'])) {

            $sql .= " AND o.date_added >= STR_TO_DATE('" . $this->db->escape($data['filter_date_added_from']) . "','%Y-%m-%d %H:%i:%s')";

        } elseif (!empty($data['filter_date_added_on'])) {

            $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added_on']) . "')";
        }

        if (!empty($data['filter_date_modified_to']) && !empty($data['filter_date_modified_from'])) {

            $sql .= " AND o.date_modified BETWEEN STR_TO_DATE('" . $this->db->escape($data['filter_date_modified_from']) . "','%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('" . $this->db->escape($data['filter_date_modified_to']) . "','%Y-%m-%d %H:%i:%s')";

        } elseif (!empty($data['filter_date_modified_from'])) {

            $sql .= " AND o.date_modified >= STR_TO_DATE('" . $this->db->escape($data['filter_date_modified_from']) . "','%Y-%m-%d %H:%i:%s')";

        } elseif (!empty($data['filter_date_modified_on'])) {

            $sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified_on']) . "')";
        }


        if (!empty($data['filter_total'])) {
            $sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
        }

        $sort_data = array(
            'o.order_id',
            'customer',
            'o.date_added',
            'o.date_modified',
            'o.total'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY o.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $orders_query = $this->db->query($sql);
        $orders = array();
        $index = 0;

        $this->load->model('localisation/language');

        foreach ($orders_query->rows as $order) {

            $payment_iso_code_2 = '';
            $payment_iso_code_3 = '';

            if (isset($order["pc_iso_code_2"])) {
                $payment_iso_code_2 = $order["pc_iso_code_2"];
            }

            if (isset($order["pc_iso_code_3"])) {
                $payment_iso_code_3 = $order["pc_iso_code_3"];
            }

            $shipping_iso_code_2 = '';
            $shipping_iso_code_3 = '';

            if (isset($order["sc_iso_code_2"])) {
                $shipping_iso_code_2 = $order["sc_iso_code_2"];
            }

            if (isset($order["sc_iso_code_3"])) {
                $shipping_iso_code_3 = $order["sc_iso_code_3"];
            }

            if (isset($order["payment_zone_code"])) {
                $payment_zone_code = $order["payment_zone_code"];
            } else {
                $payment_zone_code = '';
            }

            if (isset($order["shipping_zone_code"])) {
                $shipping_zone_code = $order["shipping_zone_code"];
            } else {
                $shipping_zone_code = '';
            }


            $language_info = $this->model_localisation_language->getLanguage($order['language_id']);

            if ($language_info) {
                $language_code = $language_info['code'];
                $language_filename = $language_info['image'];
                $language_directory = $language_info['directory'];
            } else {
                $language_code = '';
                $language_filename = '';
                $language_directory = '';
            }

            $orders[$index] =  array(
                'order_id'                => $order['order_id'],
                'invoice_no'              => $order['invoice_no'],
                'invoice_prefix'          => $order['invoice_prefix'],
                'store_id'                => $order['store_id'],
                'store_name'              => $order['store_name'],
                'store_url'               => $order['store_url'],
                'customer_id'             => $order['customer_id'],
                'firstname'               => $order['firstname'],
                'lastname'                => $order['lastname'],
                'telephone'               => $order['telephone'],
                'fax'                     => $order['fax'],
                'email'                   => $order['email'],
                'payment_firstname'       => $order['payment_firstname'],
                'payment_lastname'        => $order['payment_lastname'],
                'payment_company'         => $order['payment_company'],
                //'payment_company_id'      => $order['payment_company_id'],
                //'payment_tax_id'          => $order['payment_tax_id'],
                'payment_address_1'       => $order['payment_address_1'],
                'payment_address_2'       => $order['payment_address_2'],
                'payment_postcode'        => $order['payment_postcode'],
                'payment_city'            => $order['payment_city'],
                'payment_zone_id'         => $order['payment_zone_id'],
                'payment_zone'            => $order['payment_zone'],
                'payment_zone_code'       => $payment_zone_code,
                'payment_country_id'      => $order['payment_country_id'],
                'payment_country'         => $order['payment_country'],
                'payment_iso_code_2'      => $payment_iso_code_2,
                'payment_iso_code_3'      => $payment_iso_code_3,
                'payment_address_format'  => $order['payment_address_format'],
                'payment_method'          => $order['payment_method'],
                'payment_code'            => $order['payment_code'],
                'shipping_firstname'      => $order['shipping_firstname'],
                'shipping_lastname'       => $order['shipping_lastname'],
                'shipping_company'        => $order['shipping_company'],
                'shipping_address_1'      => $order['shipping_address_1'],
                'shipping_address_2'      => $order['shipping_address_2'],
                'shipping_postcode'       => $order['shipping_postcode'],
                'shipping_city'           => $order['shipping_city'],
                'shipping_zone_id'        => $order['shipping_zone_id'],
                'shipping_zone'           => $order['shipping_zone'],
                'shipping_zone_code'      => $shipping_zone_code,
                'shipping_country_id'     => $order['shipping_country_id'],
                'shipping_country'        => $order['shipping_country'],
                'shipping_iso_code_2'     => $shipping_iso_code_2,
                'shipping_iso_code_3'     => $shipping_iso_code_3,
                'shipping_address_format' => $order['shipping_address_format'],
                'shipping_method'         => $order['shipping_method'],
                'shipping_code'           => $order['shipping_code'],
                'comment'                 => $order['comment'],
                'total'                   => $order['total'],
                'order_status_id'         => $order['order_status_id'],
                'language_id'             => $order['language_id'],
                'language_code'           => $language_code,
                'language_filename'       => $language_filename,
                'language_directory'      => $language_directory,
                'currency_id'             => $order['currency_id'],
                'currency_code'           => $order['currency_code'],
                'currency_value'          => $order['currency_value'],
                'ip'                      => $order['ip'],
                'forwarded_ip'            => $order['forwarded_ip'],
                'user_agent'              => $order['user_agent'],
                'accept_language'         => $order['accept_language'],
                'date_modified'           => $order['date_modified'],
                'date_added'              => $order['date_added']
            );
            $index++;
        }

        return $orders;
    }

    public function getOrderOptions($order_id, $order_product_id) {
        $query = $this->db->query("
                    SELECT * FROM " . DB_PREFIX . "order_option
                    LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (" . DB_PREFIX . "order_option.product_option_value_id = pov.product_option_value_id)
                    WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

        return $query->rows;
    }

    public function getOrderHistories($order_id) {
        $query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC");

        return $query->rows;
    }


    public function getTotalOrderProductsByOrderId($order_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

        return $query->row['total'];
    }

    public function getTotalOrderVouchersByOrderId($order_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

        return $query->row['total'];
    }

    public function deleteOrder($order_id) {


        $this->db->query("DELETE FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_option` WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_fraud` WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE `or`, ort FROM `" . DB_PREFIX . "order_recurring` `or`, `" . DB_PREFIX . "order_recurring_transaction` `ort` WHERE order_id = '" . (int)$order_id . "' AND ort.order_recurring_id = `or`.order_recurring_id");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "affiliate_transaction` WHERE order_id = '" . (int)$order_id . "'");

    }

    public function setManufacturerImage($manufacturer_id, $image) {
        $this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '".$this->db->escape($image)."' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    }

    public function addManufacturer($data) {

        $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

        $manufacturer_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        }

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['keyword'])) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        $this->cache->delete('manufacturer');

        return $manufacturer_id;
    }

    public function editManufacturer($manufacturer_id, $data) {

        $this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        }

        if (isset($data['manufacturer_store'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

            foreach ($data['manufacturer_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
            }
        }
        if ($data['keyword']) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        $this->cache->delete('manufacturer');

    }

    public function deleteManufacturer($manufacturer_id) {

        $this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

        $this->cache->delete('manufacturer');

    }

    public function getManufacturer($manufacturer_id) {
        $query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "') AS keyword FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        return $query->row;
    }

    public function getManufacturers($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer";

        if (!empty($data['filter_name'])) {
            $sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'name',
            'sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getUrlAlias($keyword) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $this->db->escape($keyword) . "'");

        return $query->row;
    }


    public function setCategoryImage($category_id, $image) {
        $this->db->query("UPDATE " . DB_PREFIX . "category SET image = '".$this->db->escape($image)."', date_modified = NOW() WHERE category_id = '" . (int)$category_id . "'");
    }

    public function getCustomFieldValues($custom_field_id, $language) {
        $custom_field_value_data = array();

        $custom_field_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_value cfv LEFT JOIN " . DB_PREFIX . "custom_field_value_description cfvd ON (cfv.custom_field_value_id = cfvd.custom_field_value_id) WHERE cfv.custom_field_id = '" . (int)$custom_field_id . "' AND cfvd.language_id = '" . (int)$language . "' ORDER BY cfv.sort_order ASC");

        foreach ($custom_field_value_query->rows as $custom_field_value) {
            $custom_field_value_data[$custom_field_value['custom_field_value_id']] = array(
                'custom_field_value_id' => $custom_field_value['custom_field_value_id'],
                'name'                  => $custom_field_value['name']
            );
        }

        return $custom_field_value_data;
    }


    public function getOrderVouchers($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

        return $query->rows;
    }

    public function getOrderTotals($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

        return $query->rows;
    }

    public function getProductDiscounts($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

        return $query->rows;
    }

    public function getProductSpecials($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

        return $query->rows;
    }
    public function getOption($option_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.option_id = '" . (int)$option_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getOptionValue($optionValueId) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "option_value WHERE option_value_id = '" . (int)$optionValueId . "'");

        return $query->row['total'];
    }

    public function setOptionImage($optionValueId, $image) {
        $this->db->query("UPDATE " . DB_PREFIX . "option_value SET image = '".$this->db->escape(html_entity_decode($image, ENT_QUOTES, 'UTF-8'))."' WHERE option_value_id = '" . (int)$optionValueId . "'");
    }

    public function addCoupon($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

        $coupon_id = $this->db->getLastId();

        if (isset($data['coupon_product'])) {
            foreach ($data['coupon_product'] as $product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
            }
        }

        if (isset($data['coupon_category'])) {
            foreach ($data['coupon_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        return $coupon_id;
    }

    public function editCoupon($coupon_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "' WHERE coupon_id = '" . (int)$coupon_id . "'");


        if (isset($data['coupon_product'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
            foreach ($data['coupon_product'] as $product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
            }
        }


        if (isset($data['coupon_category'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");
            foreach ($data['coupon_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
            }
        }
    }

    public function deleteCoupon($coupon_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");
    }

    public function getCoupon($coupon_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");

        return $query->row;
    }

    public function getCoupons($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "coupon";

        $sql .= " ORDER BY name";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCouponProducts($coupon_id) {
        $coupon_product_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");

        foreach ($query->rows as $result) {
            $coupon_product_data[] = $result['product_id'];
        }

        return $coupon_product_data;
    }

    public function getCouponCategories($coupon_id) {
        $coupon_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");

        foreach ($query->rows as $result) {
            $coupon_category_data[] = $result['category_id'];
        }

        return $coupon_category_data;
    }

    public function getCouponByCode($code) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE code = '" . $this->db->escape($code) . "'");

        return $query->row;
    }
}
