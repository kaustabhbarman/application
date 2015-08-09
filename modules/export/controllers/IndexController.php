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
 * @package     Module_Export
 * @author      Sascha Szott <szott@zib.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for export function.
 *
 * The export actions are separate classes implementing the interface Export_Model_ExportPlugin and are dynamically
 * mapped to controller functions.
 */
class Export_IndexController extends Application_Controller_ModuleAccess {

    /**
     * @var array containing export plugins
     */
    private $_plugins;

    /**
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init() {
        parent::init();

        // Controller outputs plain Xml, so rendering and layout are disabled.
        $this->_helper->viewRenderer->setNoRender(true); // TODO there could be plugins requiring rendering
        $this->_helper->layout()->disableLayout();

        $this->loadPlugins();
    }

    /**
     * Returns small XML error message if access to module has been denied.
     */
    public function moduleAccessDeniedAction() {
        $response = $this->getResponse();
        $response->setHttpResponseCode(401);

        $doc = new DOMDocument();
        $doc->appendChild($doc->createElement('error', 'Unauthorized: Access to module not allowed.'));
        $this->getResponse()->setBody($doc->saveXml());
    }

    /**
     * Maps action calls to export plugins or returns an error message.
     *
     * @param  string $action     The name of the action that was called.
     * @param  array  $parameters The parameters passed to the action.
     * @return void
     */
    public function __call($action, $parameters) {
        // TODO what does this code do
        if (!'Action' == substr($action, -6)) {
            $this->getLogger()->info(__METHOD__ . ' undefined method: ' . $action);
            parent::__call($action, $parameters);
        }

        $actionName = $this->getRequest()->getActionName();

        $this->getLogger()->debug("Request to export plugin $actionName");

        $plugin = $this->getPlugin($actionName);

        if (!is_null($plugin)) {
            $plugin->init();
            $plugin->execute();
            $plugin->postDispatch();
        }
        else {
            throw new Application_Exception('Plugin ' . htmlspecialchars($actionName) . ' not found');
        }
    }

    /**
     * Returns plugin for action name.
     *
     * The plugin is setup for execution.
     *
     * @param $name Name of plugin/action.
     * @return null|Export_Model_ExportPlugin
     *
     * TODO should the namespace for plugins be limited (security)?
     */
    protected function getPlugin($name) {
        if (isset($this->_plugins[$name])) {
            $pluginConfig = $this->_plugins[$name];
            $pluginClass = $pluginConfig->class;

            $plugin = new $pluginClass($name); // TODO good design?
            $plugin->setConfig($pluginConfig);
            $plugin->setRequest($this->getRequest());
            $plugin->setResponse($this->getResponse());
            $plugin->setView($this->view);

            return $plugin;
        }
        else {
            return null;
        }
    }

    /**
     * Loads export plugins.
     *
     * Der Plugin spezifische Teil der Konfiguation wird festgehalten und später verwendet.
     */
    protected function loadPlugins() {
        $config = $this->getConfig();
        if (isset($config->plugins->export)) {
            $exportPlugins = $config->plugins->export->toArray();

            $plugins = array();

            foreach ($exportPlugins as $name => $plugin) {
                $pluginName = ($name === 'default') ? 'index' : $name;
                $plugins[$pluginName] = $config->plugins->export->$name;
            }

            $this->_plugins = $plugins;
        }
    }

}

