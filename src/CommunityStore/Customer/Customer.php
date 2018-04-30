<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Customer;

use Session;
use User;
use UserInfo;
use Concrete\Attribute\Address\Value as AddressAttributeValue;

class Customer
{
    protected $ui;

    public function __construct($uID = null)
    {
        $u = new User();

        if (!is_null($uID)) {
            $this->ui = UserInfo::getByID($uID);
        } elseif ($u->isLoggedIn()) {
            $this->ui = UserInfo::getByID($u->getUserID());
        }  else {
            $this->ui = null;
        }
    }

    public function getUserInfo()
    {
        return $this->ui;
    }

    public function setValue($handle, $value)
    {
        if ($this->isGuest()) {
            Session::set('community_' . $handle, $value);
        } else {
            $this->ui->setAttribute($handle, $value);
        }
    }

    public function getAddress($handle) {

        if ($this->isGuest()) {
            $addressraw = Session::get('community_' .$handle);
            return self::formatAddress($addressraw);
        } else {
            return (string)$this->ui->getAttribute($handle);
        }

    }

    public function getValue($handle)
    {
        if ($this->isGuest()) {
            $val = Session::get('community_' .$handle);

            if (is_array($val)) {
                return (object) $val;
            }

            return $val;
        } else {
            return $this->ui->getAttribute($handle);
        }
    }

    public function getAddressValue($handle, $valuename) {
        $att = $this->getValue($handle);
        return $this->returnAttributeValue($att,$valuename);
    }

    private static function returnAttributeValue($att, $valuename) {
        $valueCamel = camel_case($valuename);

        if (method_exists($att, 'get' .$valueCamel)) {
            $functionname = 'get'.$valueCamel;
            return $att->$functionname();
        } else {
            return $att->$valuename;
        }
    }

    public function getValueArray($handle)
    {
        if ($this->isGuest()) {
            $val = Session::get('community_' .$handle);

            return $val;
        } else {
            return $this->ui->getAttribute($handle);
        }
    }

    public function isGuest()
    {
        return is_null($this->ui);
    }

    public function getUserID()
    {
        if ($this->isGuest()) {
            return 0;
        } else {
            return $this->ui->getUserID();
        }
    }

    public function getEmail()
    {
        if ($this->isGuest()) {
            return Session::get('community_email');
        } else {
            return $this->ui->getUserEmail();
        }
    }

    public function setEmail($email)
    {
        Session::set('community_email', $email);
    }

    public function getLastOrderID()
    {
        return Session::get('community_lastOrderID');
    }

    public function setLastOrderID($id)
    {
        Session::set('community_lastOrderID', $id);
    }

    // 5.7 compatibility function
    public static function formatAddress($address)
    {
        $ret = '';
        $address1 = self::returnAttributeValue($address, 'address1');
        $address2 = self::returnAttributeValue($address, 'address2');
        $city = self::returnAttributeValue($address, 'city');
        $state_province = self::returnAttributeValue($address, 'state_province');
        $postal_code = self::returnAttributeValue($address, 'postal_code');
        $country = self::returnAttributeValue($address, 'country');

        if ($address1) {
            $ret .= $address1 . "\n";
        }
        if ($address2) {
            $ret .= $address2 . "\n";
        }
        if ($city) {
            $ret .= $city;
        }
        if ($state_province) {
            $ret .= ", ";
        }
        if ($state_province) {

            $val = \Core::make('helper/lists/states_provinces')->getStateProvinceName($state_province, $country);
            if ($val == '') {
                $ret .= $state_province;
            } else {
                $ret .= $val;
            }
        }
        if ($postal_code) {
            $ret .= " " . $postal_code;
        }
        if ($city || $state_province || $postal_code) {
            $ret .= "\n";
        }
        if ($country) {
            $ret .= \Core::make('helper/lists/countries')->getCountryName($country);
        }
        return $ret;
    }

    public static function formatAddressArray($address)
    {
        $ret = '';
        $address1 = $address['address1'];
        $address2 = $address['address2'];
        $city = $address['city'];
        $state_province = $address['state_province'];
        $postal_code = $address['postal_code'];
        $country = $address['country'];

        if ($address1) {
            $ret .= $address1 . "\n";
        }
        if ($address2) {
            $ret .= $address2 . "\n";
        }
        if ($city) {
            $ret .= $city;
        }
        if ($state_province) {
            $ret .= ", ";
        }
        if ($state_province) {

            $val = \Core::make('helper/lists/states_provinces')->getStateProvinceName($state_province, $country);
            if ($val == '') {
                $ret .= $state_province;
            } else {
                $ret .= $val;
            }
        }
        if ($postal_code) {
            $ret .= " " . $postal_code;
        }
        if ($city || $state_province || $postal_code) {
            $ret .= "\n";
        }
        if ($country) {
            $ret .= \Core::make('helper/lists/countries')->getCountryName($country);
        }
        return $ret;
    }
}
