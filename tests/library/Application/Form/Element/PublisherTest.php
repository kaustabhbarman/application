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
 * @category    Application Unit Test
 * @package     Form_Element
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_Form_Element_PublisherTest extends FormElementTestCase {

    public function setUp() {
        $this->_formElementClass = 'Application_Form_Element_Publisher';
        $this->_expectedDecoratorCount = 6;
        $this->_expectedDecorators = array('ViewHelper', 'Errors', 'Description', 'ElementHtmlTag', 'LabelNotEmpty',
            'dataWrapper');
        $this->_staticViewHelper = 'viewFormSelect';
        parent::setUp();
    }

    public function testOptions() {
        $element = $this->getElement();

        $publishers = Opus_DnbInstitute::getPublishers();

        $this->assertEquals(count($publishers), count($element->getMultiOptions()));

        $index = 0;

        foreach ($element->getMultiOptions() as $modelId => $label) {
            $this->assertEquals($publishers[$index]->getId(), $modelId);
            $this->assertEquals($publishers[$index]->getDisplayName(), $label);
            $index++;
        }
    }

    public function testValidator() {
        $element = $this->getElement();

        $this->assertTrue($element->getValidator('Int') !== false);
    }

}