<?php
/**
 * Implemented by scope01 GmbH team https://scope01.com
 *
 * @copyright scope01 GmbH https://scope01.com
 * @license MIT License
 * @link https://scope01.com
 */

namespace ScopRedirecter;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ScopRedirecter\Models\Redirecter;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Shopware-Plugin ScopRedirecter.
 */
class ScopRedirecter extends Plugin
{
    /**
     * Adds the widget to the database and creates the database schema.
     *
     * @param Plugin\Context\InstallContext $installContext
     */
    public function install(InstallContext $installContext)
    {
        $schemaManager = Shopware()->Container()->get('dbal_connection')->getSchemaManager();

        if ($schemaManager->tablesExist(['scop_redirecter']) === false) {
            $this->createTables();
        } else {
            $this->updateTables();
        }

        parent::install($installContext);
    }

    public function update(UpdateContext $updateContext)
    {
        $this->updateTables();
        parent::update($updateContext);
    }

    public function deactivate(DeactivateContext $context)
    {
        parent::deactivate($context);
    }

    public function activate(ActivateContext $context)
    {
        parent::activate($context);
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        if (!$context->keepUserData()) {
            $this->removeTables();
        }
        if ($context->getPlugin()->getActive()) {
            $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('scop_redirecter.plugin_dir', $this->getPath());
        parent::build($container);
    }

    /**
     * creates database tables on base of doctrine models
     *
     */
    private function createTables()
    {
        $modelManager = Shopware()->Models();
        $tool = new SchemaTool($modelManager);

        $classes = [
            $modelManager->getClassMetadata(Redirecter::class)
        ];

        $tool->createSchema($classes);
    }

    /**
     * creates database tables on base of doctrine models
     *
     */
    private function updateTables()
    {
        $modelManager = Shopware()->Models();
        $tool = new SchemaTool($modelManager);

        $classes = [
            $modelManager->getClassMetadata(Redirecter::class)
        ];

        $tool->updateSchema($classes, true);
    }

    /**
     * removes created tables
     */
    private function removeTables()
    {
        $modelManager = Shopware()->Models();
        $tool = new SchemaTool($modelManager);
        $classes = [
            $modelManager->getClassMetadata(Redirecter::class)
        ];
        $tool->dropSchema($classes);
    }
}
