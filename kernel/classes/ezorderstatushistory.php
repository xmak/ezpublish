<?php
//
// Definition of eZOrderStatusHistory class
//
// Created on: <07-Apr-2005 16:27:14 amos>
//
// Copyright (C) 1999-2005 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// Licencees holding a valid "eZ publish professional licence" version 2
// may use this file in accordance with the "eZ publish professional licence"
// version 2 Agreement provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" version 2 is available at
// http://ez.no/ez_publish/licences/professional/ and in the file
// PROFESSIONAL_LICENCE included in the packaging of this file.
// For pricing of this licence please contact us via e-mail to licence@ez.no.
// Further contact information is available at http://ez.no/company/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file ezorderstatushistory.php
*/

/*!
  \class eZOrderStatusHistory ezorderstatushistory.php
  \brief Handles a list of status changes to an order item.

  This uses the database table ezorder_status_history to store changes
  in status for an order item.
  Each entry consists of the new status, the time it was done and the
  person who did the change.

  The \c order_id refers to the external ID of an order (\c order_nr)
  and not the internal auto increment value.

  To fetch a given history element use fetch() with the history ID.
  If you intend to display the history elements for an order item use
  the fetchListByOrder() function which returns the history sorted by
  date (newest first).
  If you are interested in the number of history elements for an order
  use the fetchCount() function.

  If you intend to create a new history element use the create() function.

*/

include_once( "kernel/classes/ezpersistentobject.php" );

class eZOrderStatusHistory extends eZPersistentObject
{
    /*!
     Initialises the persistent object with \a $row.
     If \c status_name is present in \a $row it will cache it in the $StatusName variable.
    */
    function eZOrderStatusHistory( $row )
    {
        $this->eZPersistentObject( $row );
        $this->Modifier = null;
        $this->StatusName = null;
        if ( isset( $row['status_name'] ) )
            $this->StatusName = $row['status_name'];
    }

    /*!
     \return the persistent object definition for the eZOrderStatusHistory class.
    */
    function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "order_id" => array( 'name' => 'OrderID',
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         "status_id" => array( 'name' => 'StatusID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "modifier_id" => array( 'name' => "ModifierID",
                                                                 'datatype' => 'integer',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         "modified" => array( 'name' => "Modified",
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ) ),
                      'function_attributes' => array( 'modifier' => 'modifier',
                                                      'status_name' => 'fetchOrderStatusName',
                                                      'status' => 'fetchOrderStatus' ),
                      "keys" => array( "id" ),
                      "increment_key" => "id",
                      "class_name" => "eZOrderStatusHistory",
                      "name" => "ezorder_status_history" );
    }

    /*!
      \return The user which modified the status, this is returned as a content object.
      \note The field \c modified_id is used to find the user, this will contain
            the content object ID of the user.
    */
    function &modifier()
    {
        if ( $this->Modifier === null )
        {
            include_once( 'kernel/classes/ezcontentobject.php' );
            $this->Modifier =& eZContentObject::fetch( $this->ModifierID );
        }
        return $this->Modifier;
    }

    /*!
     \return The order status object for this history entry.
     \sa fetchOrderStatusName()
    */
    function &fetchOrderStatus()
    {
        include_once( 'kernel/classes/ezorderstatus.php' );
        $statusList = eZOrderStatus::fetchMap( true, true );
        if ( isset( $statusList[$this->StatusID] ) )
            return $statusList[$this->StatusID];
        $retValue = false;
        return $retValue;
    }

    /*!
     \return The name of the order status for this history entry.
     \sa fetchOrderStatus()
    */
    function &fetchOrderStatusName()
    {
        if ( $this->StatusName === null )
        {
            $status = $this->fetchOrderStatus();
            $this->StatusName = $status->attribute( 'name' );
        }
        return $this->StatusName;
    }

    /*!
     \static
     \return the status history object with the given DB ID.
    */
    function &fetch( $id, $asObject = true )
    {
        return eZPersistentObject::fetchObject( eZOrderStatusHistory::definition(),
                                                null,
                                                array( "id" => $id ),
                                                $asObject );
    }

    /*!
     \static
     \param $asObject If \c true return them as objects.
     \return A list of defined orders sorted by status ID.
    */
    function fetchListByOrder( $orderID, $asObject = true )
    {
        $db =& eZDB::instance();

        $orderID = (int)$orderID;
        $rows = $db->arrayQuery(  "SELECT ezorder_status_history.*, ezorder_status.name AS status_name\n" .
                                  "FROM ezorder_status_history, ezorder_status\n" .
                                  "WHERE order_id = $orderID AND\n" .
                                  "      ezorder_status.status_id = ezorder_status_history.status_id\n" .
                                  "ORDER BY ezorder_status_history.modified DESC" );

        return eZPersistentObject::handleRows( $rows, 'eZOrderStatusHistory', $asObject );
    }

    /*!
     \static
     \param $asObject If \c true return them as objects.
     \return A list of defined orders sorted by status ID.
    */
    function fetchCount( $orderID, $asObject = true )
    {
        $db =& eZDB::instance();

        $orderID = (int)$orderID;
        $countArray = $db->arrayQuery(  "SELECT count( * ) AS count FROM ezorder_status_history WHERE order_id = $orderID" );
        return $countArray[0]['count'];
    }

    /*!
     \static
     \return A new eZOrderStatusHistory object initialized with the input parameters.
    */
    function create( $orderID, $statusID, $userID = false, $timestamp = false )
    {
        if ( $timestamp === false )
            $timestamp = mktime();
        if ( $userID === false )
            $userID = eZUser::currentUserID();
        $row = array( 'id' => null,
                      'order_id' => $orderID,
                      'status_id' => $statusID,
                      'modifier_id' => $userID,
                      'modified' => $timestamp );
        return new eZOrderStatusHistory( $row );
    }


    /// \privatesection
    /// This is used for caching the current modifier,
    /// it will either contain \c null (uncached) or a content object (cached).
    var $Modifier;
}

?>
