<?php

namespace ScopRedirecter;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
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
        parent::install($installContext);
        $this->createNewTables();
    }

    public function update(UpdateContext $updateContext)
    {
        $updateContext->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
        $this->createNewTables();
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        if (!$context->keepUserData()) {
            $this->removeTables($context);
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
    private function createNewTables()
    {
        $schemaManager = Shopware()->Container()->get('models')->getConnection()->getSchemaManager();
        $tool = new SchemaTool($this->container->get('models'));

        //getting Redirecter Class
        $classes = [
            $this->container->get('models')->getClassMetadata(Redirecter::class)
        ];

        //checking if tables exist and if not => create new table
        foreach ($classes as $class) {
            if (!$schemaManager->tablesExist([$class->getTableName()])) {
                $tool->createSchema([$class]);
            } else {
                $tool->updateSchema([$class], true);
            }
        }
    }

    /**
     * removes created tables
     */
    private function removeTables(UninstallContext $context)
    {
        $modelManager = Shopware()->Models();
        $tool = new SchemaTool($this->container->get('models'));
        $classes = [
            $modelManager->getClassMetadata(Redirecter::class)
        ];
        $tool->dropSchema($classes);
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
