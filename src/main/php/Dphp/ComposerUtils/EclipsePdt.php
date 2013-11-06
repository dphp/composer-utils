<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Generate Eclipse PDT project from composer.json.
 *
 * LICENSE: See LICENSE.md
 *
 * COPYRIGHT: See COPYRIGHT.md
 *
 * @package Dphp
 * @subpackage ComposerUtils
 * @author Thanh Ba Nguyen <btnguyen2k@gmail.com>
 * @copyright See COPYRIGHT.md
 * @license See LICENSE.md
 * @version $Id$
 * @since File available since v0.1
 */
namespace Dphp\ComposerUtils;

/**
 * Generate Eclipse PDT project from composer.json.
 *
 * @package Dphp
 * @subpackage ComposerUtils
 * @author Thanh Ba Nguyen <btnguyen2k@gmail.com>
 * @copyright See COPYRIGHT.md
 * @license See LICENSE.md
 * @version $Id$
 * @since Class available since v0.1
 */
class EclipsePdt {
    /* load composer.json */
    private static function _loadComposerJson() {
        $composerJson = file_get_contents ( "composer.json" );
        if ($composerJson === FALSE) {
            throw new \Exception ( "File composer.json not found or not readable!" );
        }
        return json_decode ( $composerJson, TRUE );
    }
    
    /* generate .project file */
    private static function _generateFileProject($projName) {
        echo "Generating file [.project]...\n";
        
        $filename = ".project";
        if (! file_exists ( $filename )) {
            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<projectDescription>
	<name>{$projName}</name>
	<comment></comment>
	<projects>
	</projects>
	<buildSpec>
		<buildCommand>
			<name>org.eclipse.wst.validation.validationbuilder</name>
			<arguments>
			</arguments>
		</buildCommand>
		<buildCommand>
			<name>org.eclipse.dltk.core.scriptbuilder</name>
			<arguments>
			</arguments>
		</buildCommand>
		<buildCommand>
			<name>org.eclipse.wst.common.project.facet.core.builder</name>
			<arguments>
			</arguments>
		</buildCommand>
	</buildSpec>
	<natures>
		<nature>org.eclipse.wst.common.project.facet.core.nature</nature>
		<nature>org.eclipse.php.core.PHPNature</nature>
	</natures>
</projectDescription>            
XML;
            $project = new \SimpleXMLElement ( $xml );
        } else {
            $fileContent = file_get_contents ( $filename );
            $project = new \SimpleXMLElement ( $fileContent );
            $project->name [0] = $projName;
        }
        $project->asXML ( $filename );
    }
    
    /* generate .buildpath file */
    private static function _generateFileBuildpath() {
        echo "Generating file [.buildpath]...\n";
        
        $filename = ".buildpath";
        if (! file_exists ( $filename )) {
            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<buildpath>
	<buildpathentry kind="src" path="src/main/php"/>
	<buildpathentry kind="con" path="org.eclipse.php.core.LANGUAGE"/>
</buildpath>
XML;
            file_put_contents ( $filename, $xml, 0 );
        }
    }
    
    /* generate .gitignore file */
    private static function _generateFileGitIgnore() {
        echo "Generating file [.gitignore]...\n";
        
        $filename = ".gitignore";
        $lines = file_exists ( $filename ) ? file ( $filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) : Array ();
        
        // build ignore list
        $map = Array (
                '.buildpath' => TRUE,
                '.project' => TRUE,
                '.settings' => TRUE,
                'vendor' => TRUE,
                'composer.lock' => TRUE 
        );
        foreach ( $lines as $line ) {
            $map [$line] = TRUE;
        }
        
        // assembly file content
        $filecontent = '';
        foreach ( $map as $k => $v ) {
            $filecontent .= "$k\n";
        }
        
        file_put_contents ( $filename, $filecontent, 0 );
    }
    
    /* generate project directory structure */
    private static function _generateProjectStructure($vendorName) {
        echo "Generating Eclipse PDT project structure...\n";
        
        $vendorName = ucwords ( $vendorName );
        @mkdir ( "src/main/php/$vendorName", 0755, TRUE );
        @mkdir ( "src/test/php/$vendorName", 0755, TRUE );
        
        $php = <<<PHP
<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
namespace {$vendorName};

require_once 'vendor/autoload.php';

/**
 * A dummy PHPUnit test case that can be used as a template.
 *
 * @package {$vendorName}
 * @author {$vendorName}
 * @copyright (C) {$vendorName}
 */
class DummyTest extends \PHPUnit_Framework_TestCase {
    public function testDummy() {
        \$this->assertTrue(TRUE);        
    }
}
PHP;
        file_put_contents("src/test/php/$vendorName/DummyTest.php", $php, 0);
        
        @mkdir ( ".settings", 0755, TRUE );
        
        $filename = ".settings/org.eclipse.core.resources.prefs";
        if (! file_exists ( $filename )) {
            file_put_contents($filename, "eclipse.preferences.version=1", 0);
        }
        
        $filename = ".settings/org.eclipse.php.core.prefs";
        if (! file_exists ( $filename )) {
            file_put_contents($filename, "eclipse.preferences.version=1", 0);
        }
        
        $filename = ".settings/org.eclipse.wst.common.project.facet.core.xml";
        if (! file_exists ( $filename )) {
            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<faceted-project>
  <fixed facet="php.component"/>
  <fixed facet="php.core.component"/>
  <installed facet="php.core.component" version="1"/>
  <installed facet="php.component" version="5.3"/>
</faceted-project>
XML;
            file_put_contents($filename, $xml, 0);
        }
    }
    
    /**
     * Creates Eclipse PDT 3 project structure.
     */
    public static function createPdt3Project() {
        $composerJson = self::_loadComposerJson ();
        
        $tokens = explode ( "/", $composerJson ["name"] );
        $VENDOR = is_array ( $tokens ) && count ( $tokens ) > 0 ? $tokens [0] : NULL;
        $PROJ_NAME = is_array ( $tokens ) && count ( $tokens ) > 1 ? $tokens [1] : NULL;
        if ($PROJ_NAME == NULL) {
            throw new \Exception ( "Invalid composer.json: name attribute not found or invalid!" );
        }
        
        self::_generateProjectStructure ( $VENDOR );
        self::_generateFileProject ( $PROJ_NAME );
        self::_generateFileBuildpath ();
        self::_generateFileGitIgnore ();
        
        echo "DONE.\n";
    }
}
