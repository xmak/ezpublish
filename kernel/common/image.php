<?php
//
// Definition of Image class
//
// Created on: <08-May-2002 10:15:05 amos>
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

function &imageInit()
{
    include_once( 'lib/ezimage/classes/ezimagemanager.php' );
    include_once( 'lib/ezimage/classes/ezimageshell.php' );
    include_once( 'lib/ezimage/classes/ezimagegd.php' );

    $img =& $GLOBALS['eZPublishImage'];
    if ( get_class( $img ) == 'ezimagemanager' )
        return $img;

    $img =& eZImageManager::instance();

    $ini =& eZINI::instance();
    if ( $ini->variable( 'ImageSettings', 'ScaleLargerThenOriginal' ) == 'true' )
        $geometry = '-geometry "%wx%h"';
    else
        $geometry = '-geometry "%wx%h>"';

    $imgINI =& eZINI::instance( 'image.ini' );

    $useConvert = $imgINI->variable( 'ConverterSettings', 'UseConvert' ) == 'true';
    $useGD = $imgINI->variable( 'ConverterSettings', 'UseGD' ) == 'true';

    if ( $useConvert and
         !eZImageShell::isAvailable( 'convert' ) )
    {
        eZDebug::writeError( "Convert is not available, disabling", 'imageInit' );
        $useConvert = false;
    }
    if ( $useGD and
         !eZImageGD::isAvailable() )
    {
        eZDebug::writeError( "ImageGD is not available, disabling", 'imageInit' );
        $useGD = false;
    }

    if ( !$useConvert and
         !$useGD )
    {
        eZDebug::writeError( "No conversion types available", 'imageInit' );
    }

    // Register convertors
    if ( $useConvert )
    {
        $img->registerType( 'convert', new eZImageShell( $imgINI->variable( 'ShellSettings', 'ConvertPath' ), $imgINI->variable( 'ShellSettings', 'ConvertExecutable' ),
                                                         array(), array(),
                                                         array( eZImageShell::createRule( $geometry,
                                                                                          'modify/scale' ),
                                                                eZImageShell::createRule( '-colorspace GRAY',
                                                                                          'colorspace/gray' ) ),
                                                         EZ_IMAGE_KEEP_SUFFIX,
                                                         EZ_IMAGE_PREPEND_SUFFIX_TAG ) );
    }
    if ( $useGD )
    {
        $img->registerType( 'gd', new eZImageGD( EZ_IMAGE_KEEP_SUFFIX,
                                                 EZ_IMAGE_PREPEND_SUFFIX_TAG ) );
    }

    // Output types
    $types = $imgINI->variableArray( 'OutputSettings', 'AvailableMimeTypes' );
    if ( count( $types ) == 0 )
        $types = array( 'image/jpeg',
                        'image/png' );

    $rules = array();
    $defaultRule = null;
    $ruleGroup = $imgINI->group( 'Rules', true );
    foreach ( $ruleGroup as $rule )
    {
        $items = explode( ';', $rule[1] );
        if ( $rule[0] == 'Rule' )
        {
            $sourceMIME = $items[0];
            $destMIME = $items[1];
            $type = $items[2];
            if ( $type == 'convert' or
                 $type == 'gd' )
            {
                $rules[] = $img->createRule( $sourceMIME, $destMIME, $type, true, true );
            }
        }
        else if ( $rule[0] == 'DefaultRule' )
        {
            $destMIME = $items[0];
            $type = $items[1];
            if ( $type == 'convert' or
                 $type == 'gd' )
            {
                $defaultRule = $img->createRule( '*', $destMIME, $type, true, true );
            }
        }
    }

//     $rules = array( $img->createRule( 'image/jpeg', 'image/jpeg', 'convert', true, true ),
//                     $img->createRule( 'image/png', 'image/png', 'convert', true, true ),
//                     $img->createRule( 'image/gif', 'image/png', 'convert', true, true ),
//                     $img->createRule( 'image/xpm', 'image/png', 'convert', true, true ) );

//     $rules = array( $img->createRule( 'image/jpeg', 'image/jpeg', 'gd', true, true ),
//                     $img->createRule( 'image/png', 'image/png', 'gd', true, true ),
//                     $img->createRule( 'image/gif', 'image/png', 'convert', true, true ),
//                     $img->createRule( 'image/xpm', 'image/png', 'convert', true, true ) );

    $mime_rules = array();
    $mimeGroup = $imgINI->group( 'MimeTypes', true );
    foreach ( $mimeGroup as $mime )
    {
        $items = explode( ';', $mime[1] );
        $mimeType = $items[0];
        $regexp = $items[1];
        $suffix = $items[2];
        $mime_rules[] = $img->createMIMEType( $mimeType, $regexp, $suffix );
    }

//     $mime_rules = array( $img->createMIMEType( 'image/jpeg', '\.jpe?g$', 'jpg' ),
//                          $img->createMIMEType( 'image/png', '\.png$', 'png' ),
//                          $img->createMIMEType( 'image/gif', '\.gif$', 'gif' ),
//                          $img->createMIMEType( 'image/xpm', '\.xpm$', 'xpm' ),
//                          $img->createMIMEType( 'image/tiff', '\.tiff$', 'tiff' ),
//                          $img->createMIMEType( 'image/ppm', '\.ppm$', 'ppm' ),
//                          $img->createMIMEType( 'image/tga', '\.tga$', 'tga' ),
//                          $img->createMIMEType( 'image/svg', '\.svg$', 'svg' ),
//                          $img->createMIMEType( 'image/wml', '\.wml$', 'wml' ),
//                          $img->createMIMEType( 'image/bmp', '\.bmp$', 'bmp' ) );

    $img->setOutputTypes( $types );
    if ( $defaultRule === null )
        $defaultRule = $img->createRule( '*', 'image/jpeg', 'convert', true, true );
    $img->setRules( $rules, $defaultRule );
//     $img->setRules( $rules, $img->createRule( '*', 'image/jpeg', 'convert', true, true ) );
    $img->setMIMETypes( $mime_rules );

    return $img;
}

?>
