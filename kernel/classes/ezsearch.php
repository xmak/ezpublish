<?php
//
// Definition of eZSearch class
//
// Created on: <25-Jun-2002 10:56:09 bf>
//
// Copyright (C) 1999-2002 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ publish professional licences" may use this
// file in accordance with the "eZ publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" is available at
// http://ez.no/home/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*!
  \class eZSearch
  \ingroup eZKernel
  \brief eZSearch handles indexing of objects to the search engine

*/

include_once( "lib/ezutils/classes/ezini.php" );

class eZSearch
{
    /*!
    */
    function eZSearch()
    {

    }

    /*!
     \static
     Will remove the index from the given object from the search engine
    */
    function removeObject( $contentObject )
    {
        $ini =& eZINI::instance();

        $searchEngineString = "ezsearch";
        if ( $ini->hasVariable( "SearchSettings", "SearchEngine" ) == true )
        {
            $searchEngineString = $ini->variable( "SearchSettings", "SearchEngine" );
        }

        // fetch the correct search engine implementation
        include_once( "kernel/search/plugins/" . strToLower( $searchEngineString ) . "/" . strToLower( $searchEngineString ) . ".php" );
        $searchEngine = new $searchEngineString;

        $searchEngine->removeObject( $contentObject );
    }

    /*!
     \static
     Will index the content object to the search engine.
    */
    function addObject( $contentObject )
    {
        $ini =& eZINI::instance();

        $searchEngineString = "ezsearch";
        if ( $ini->hasVariable( "SearchSettings", "SearchEngine" ) == true )
        {
            $searchEngineString = $ini->variable( "SearchSettings", "SearchEngine" );
        }

        // fetch the correct search engine implementation
        include_once( "kernel/search/plugins/" . strToLower( $searchEngineString ) . "/" . strToLower( $searchEngineString ) . ".php" );
        $searchEngine = new $searchEngineString;

        $searchEngine->addObject( $contentObject, "/content/view/", $metaData );
    }

    /*!
     \static
     Runs a query to the search engine.
    */
    function &search( $searchText, $params )
    {
        $ini =& eZINI::instance();

        $searchEngineString = "ezsearch";
        if ( $ini->hasVariable( "SearchSettings", "SearchEngine" ) == true )
        {
            $searchEngineString = $ini->variable( "SearchSettings", "SearchEngine" );
        }

        include( "kernel/search/plugins/" . strToLower( $searchEngineString ) . "/" . strToLower( $searchEngineString ) . ".php" );
        $searchEngine = new $searchEngineString;

        return $searchEngine->search( $searchText, $params );
    }

}

?>
