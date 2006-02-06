<?php
//
// Definition of eZVatType class
//
// Created on: <26-Nov-2002 16:00:45 wy>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.7.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*!
  \class eZVatType ezvattype.php
  \brief eZVatType handles different VAT types
  \ingroup eZKernel

*/

include_once( "kernel/classes/ezpersistentobject.php" );

class eZVatType extends eZPersistentObject
{
    /*!
    */
    function eZVatType( $row )
    {
        $this->eZPersistentObject( $row );
    }

    function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "name" => array( 'name' => "Name",
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         "percentage" => array( 'name' => "Percentage",
                                                                'datatype' => 'float',
                                                                'default' => 0,
                                                                'required' => true ) ),
                      "keys" => array( "id" ),
                      "increment_key" => "id",
                      "class_name" => "eZVatType",
                      "name" => "ezvattype" );
    }

    function fetch( $id, $asObject = true )
    {
        return eZPersistentObject::fetchObject( eZVatType::definition(),
                                                null,
                                                array( "id" => $id
                                                      ),
                                                $asObject );
    }

    function fetchList( $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZVatType::definition(),
                                                    null, null, array( 'id' => false ), null,
                                                    $asObject );
    }

    function create()
    {
        $row = array(
            "id" => null,
            "name" => ezi18n( 'kernel/shop', 'VAT type' ),
            "percentage" => null );
        return new eZVatType( $row );
    }

    /*!
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
     */
    function remove ( $id )
    {
        eZPersistentObject::removeObject( eZVatType::definition(),
                                          array( "id" => $id ) );
    }
}

?>
