<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @package     Tests
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Oai_Model_DocumentListTest extends ControllerTestCase {

    /**
     * Testet, ob beim MetaDataPrefix "epicur" nur Dokumente mit URN ausgegeben werden.
     */
    public function testDocumentOutputUrn() {
        $docWithUrn = $this->createTestDocument();
        $docWithUrn->setServerState('published');
        $identifier = new Opus_Identifier();
        $identifier->setValue('urn_value1');
        $identifier->setType('urn');
        $docWithUrn->addIdentifier($identifier);
        $docWithUrnId = $docWithUrn->store();

        $docWoUrn = $this->createTestDocument();
        $docWoUrn->setServerState('published');
        $docWoUrnId = $docWoUrn->store();

        $oaiRequest = array('metadataPrefix' => 'epicur');
        $docListModel = new Oai_Model_DocumentList();
        $docListModel->deliveringDocumentStates = array('published');
        $docIds = $docListModel->query($oaiRequest);

        $this->assertTrue(in_array($docWithUrnId, $docIds), 'Document with URN is not returned.');
        $this->assertFalse(in_array($docWoUrnId, $docIds), 'Document without URN is returned.');
    }

    /**
     * Test list document ids, metadataPrefix=XMetaDissPlus, different intervals
     * list possible intervals containing "2010-06-05"
     */
    public function testIntervalOAIPMHQueries() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $this->docId = $doc->store();
        
        $doc = new Opus_Document($this->docId);        
        $serverDateModified = $doc->getServerDateModified();
        
        $today = new DateTime();
        $today->setDate(
                $serverDateModified->getYear(),
                $serverDateModified->getMonth(),
                $serverDateModified->getDay());
        
        $yesterday = clone $today;
        $yesterday->modify('-1 day');
        
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');
        
        $todayStr = date_format($today, 'Y-m-d');
        $yesterdayStr = date_format($yesterday, 'Y-m-d');
        $tomorrowStr = date_format($tomorrow, 'Y-m-d');         

        $intervals = array(
            array(),
            array('from' => $todayStr),
            array('until' => $todayStr),
            array('from' => $yesterdayStr),
            array('until' => $tomorrowStr),
            array('from' => $todayStr, 'until' => $todayStr),
            array('from' => $yesterdayStr, 'until' => $todayStr),
            array('from' => $todayStr, 'until' => $tomorrowStr),
            array('from' => $yesterdayStr, 'until' => $tomorrowStr),
        );

        foreach ($intervals AS $interval) {
            $oaiRequest = array('verb' => 'ListRecords', 'metadataPrefix' => 'XMetaDissPlus');
            $oaiRequest = array_merge($interval, $oaiRequest);

            $docListModel = new Oai_Model_DocumentList();
            $docListModel->deliveringDocumentStates = array('published', 'deleted');
            $docListModel->xMetaDissRestriction = array();
            $docIds = $docListModel->query($oaiRequest);

            $this->assertTrue(in_array($this->docId, $docIds), "Response must contain document id $this->docId: " . var_export($interval, true));
        }
    }

    /**
     * Test list document ids, metadataPrefix=XMetaDissPlus, different intervals
     * list possible intervals *NOT* containing "2010-06-05"
     */
    public function testIntervalOAIPMHQueryWithoutTestDoc() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $this->docId = $doc->store();
        
        $doc = new Opus_Document($this->docId);        
        $serverDateModified = $doc->getServerDateModified();
        
        $today = new DateTime();
        $today->setDate(
                $serverDateModified->getYear(),
                $serverDateModified->getMonth(),
                $serverDateModified->getDay());
        
        $yesterday = clone $today;
        $yesterday->modify('-1 day');
        
        $dayBeforeYesterday = clone $yesterday;
        $dayBeforeYesterday->modify('-1 day');
        
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');
        
        $dayAfterTomorrow = clone $tomorrow;
        $dayAfterTomorrow->modify('+1 day');        
        
        $yesterdayStr = date_format($yesterday, 'Y-m-d');
        $dayBeforeYesterdayStr = date_format($dayBeforeYesterday, 'Y-m-d');
        $tomorrowStr = date_format($tomorrow, 'Y-m-d');        
        $dayAfterTomorrowStr = date_format($dayAfterTomorrow, 'Y-m-d');
        
        $intervals = array(
            array('from' => $tomorrowStr),
            array('until' => $yesterdayStr),
            array('from' => $tomorrowStr, 'until' => $dayAfterTomorrowStr),
            array('from' => $dayBeforeYesterdayStr, 'until' => $yesterdayStr),
        );

        foreach ($intervals AS $interval) {
            $oaiRequest = array('verb' => 'ListRecords', 'metadataPrefix' => 'XMetaDissPlus');
            $oaiRequest = array_merge($interval, $oaiRequest);

            $docListModel = new Oai_Model_DocumentList();
            $docListModel->deliveringDocumentStates = array('published', 'deleted');
            $docListModel->xMetaDissRestriction = array();
            $docIds = $docListModel->query($oaiRequest);

            $this->assertFalse(in_array($this->docId, $docIds), "Response must NOT contain document id $this->docId: " . var_export($interval, true));
        }
    }

}
