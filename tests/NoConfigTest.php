<?php

namespace ScopRedirecter\Tests;

use GuzzleHttp;
use GuzzleHttp\TransferStats;
use PHPUnit\Framework\TestCase;
use ScopRedirecter\ScopRedirecter as Plugin;

class NoConfigTest extends TestCase
{

    protected static $ensureLoadedPlugins = [
        'ScopRedirecter' => []
    ];
    protected $plugin;

    protected $set = [
        ["/details", "/checkout/cart/", 301],
        ["/test", "/account/", 301],
        ["/googling", "www.google.com", 302],
        ["/google", "/account/", 302],
        ["/men", "/checkout", 301],
        ["/women", "/checkout?c=5", 301]
    ];

    public function setUp(): void
    {
        $cacheManager = Shopware()->container()->get('shopware.cache_manager');

        $cacheManager->clearConfigCache();
        $cacheManager->clearSearchCache();
        $cacheManager->clearRewriteCache();
        $cacheManager->clearTemplateCache();
        $cacheManager->clearHttpCache();
        $cacheManager->clearHttpCache();
        $cacheManager->clearProxyCache();
        $cacheManager->clearOpCache();

        $this->setConfig('dontAddSlash', true);

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function tearDown(): void
    {
        $connection = Shopware()->Container()->get('dbal_connection');
        $queryBuilder = $connection->createQueryBuilder();
        $testSets = $this->set;
        $setCount = count($testSets);

        for ($i = 0; $i < $setCount; $i++) {
            $queryBuilder
                ->delete('scop_redirecter')
                ->where('scop_redirecter.start_url = "' . $testSets[$i][0] . '"');
            $queryBuilder->execute();
        }
        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['ScopRedirecter'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    public function testRunRedirects()
    {

        $host = Shopware()->Config()->base_path;

        $connection = Shopware()->Container()->get('dbal_connection');
        $queryBuilder = $connection->createQueryBuilder();
        $testSets = $this->set;
        $setCount = count($testSets);

        for ($i = 0; $i < $setCount; $i++) {
            $queryBuilder
                ->insert('scop_redirecter')
                ->values(['start_url' => '?', 'target_url' => '?', 'http_code' => '?',])
                ->setParameters([
                    0 => $testSets[$i][0],
                    1 => $testSets[$i][1],
                    2 => $testSets[$i][2],
                ]);
            $queryBuilder->execute();
        }


        $client = new GuzzleHttp\Client(['base_url' => 'http://' . $host]);


// Getting plugin Configuration
        $pluginConfig = Shopware()->container()->get('shopware.plugin.config_reader')->getByPluginName('ScopRedirecter');
        if (is_array($pluginConfig)) {
            if (isset($pluginConfig['dontAddSlash'])) {
                $pluginValue = $pluginConfig['dontAddSlash'];
            }
        }

        $this->setConfig('dontAddSlash', !$pluginValue);

//        Checking the Plugin with different opposite configuration

        //test all the created redirects
        $response = $client->get($testSets[0][0], ['allow_redirects' => true,]);
        $this->assertSame($response->getEffectiveUrl() , 'http://' . $host . $testSets[0][1] );

        $response = $client->get($testSets[1][0], ['allow_redirects' => true,]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[1][1]);

        $response = $client->get($testSets[2][0], ['allow_redirects' => true,]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $testSets[2][1]);

        $response = $client->get($testSets[3][0], ['allow_redirects' => true,]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[3][1] );

        // Check if the page will be "/checkout" for redirect url "/checkout"
        $response = $client->get($testSets[4][0], ['allow_redirects' => true,]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[4][1] . "/");

        // Check if the page will be "/checkout?c=5" for redirect url "/checkout?c=5"
        $response = $client->get($testSets[5][0], ['allow_redirects' => true,]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[5][1] );


        return $testSets;
    }

    protected function setConfig($name, $value)
    {
        Shopware()->Container()->get('config_writer')->save($name, $value);
        Shopware()->Container()->get('cache')->clean();
        Shopware()->Container()->get('config')->setShop(Shopware()->Shop());
    }

}
