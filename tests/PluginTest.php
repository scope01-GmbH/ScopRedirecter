<?php
/**
 * Implemented by scope01 GmbH team https://scope01.com
 *
 * @copyright scope01 GmbH https://scope01.com
 * @license MIT License
 * @link https://scope01.com
 */

namespace ScopRedirecter\Tests;

use GuzzleHttp;
use GuzzleHttp\TransferStats;
use PHPUnit\Framework\TestCase;
use ScopRedirecter\ScopRedirecter as Plugin;
use Shopware\Models\Shop\Shop;

class PluginTest extends TestCase
{
    /**
     * list of plugins to load
     *
     * @var array[]
     */
    protected static $ensureLoadedPlugins = [
        'ScopRedirecter' => []
    ];

    /**
     * original config value from plugin
     *
     * @var bool
     */
    protected $originalConfigValue = false;

    /**
     * dummy redirects and expected results
     *
     * @var array[]
     */
    protected $set = [
        ["/details", "/checkout/cart/", 301],
        ["/test", "/account/", 301],
        ["/googling", "www.google.com", 302],
        ["/google", "/account/", 302],
        ["/men", "/checkout/", 301],
        ["/women", "/checkout?c=5", 301],
        ["/dummy", "/index", 301]
    ];

    /**
     * create testdata
     */
    public function setUp(): void
    {
        parent::setUp();

        $pluginConfig = Shopware()->container()->get('shopware.plugin.config_reader')->getByPluginName('ScopRedirecter');
        if (\is_array($pluginConfig)) {
            if (isset($pluginConfig['dontAddSlash'])) {
                $this->originalConfigValue = (bool) $pluginConfig['dontAddSlash'];
            }
        }

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

    }

    /**
     * reset generated testdata
     */
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

        $this->setConfig('dontAddSlash', $this->originalConfigValue, false);

        parent::tearDown();
    }

    /**
     * running create instance tests
     */
    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['ScopRedirecter'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * running redirect tests
     */
    public function testRunRedirects()
    {
        $testSets = $this->set;

        /** @var Shop $shop */
        $shop = Shopware()->Models()->getRepository(Shop::class)->find(1);
        $host = (string) $shop->getHost();

        $client = new GuzzleHttp\Client(['base_url' => 'http://' . $host]);

        $this->setConfig('dontAddSlash', false);

        //test status codes
        $response = $client->get($testSets[0][0], ['allow_redirects' => false]);
        $this->assertSame($response->getStatusCode(), $testSets[0][2]);

        $response = $client->get($testSets[2][0], ['allow_redirects' => false]);
        $this->assertSame($response->getStatusCode(), $testSets[2][2]);

        //test all the created redirects
        $response = $client->get($testSets[0][0], ['allow_redirects' => true]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[0][1]);

        $response = $client->get($testSets[1][0], ['allow_redirects' => true]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[1][1]);

        $response = $client->get($testSets[2][0], ['allow_redirects' => true]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $testSets[2][1]);

        $response = $client->get($testSets[3][0], ['allow_redirects' => true]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[3][1]);

        $response = $client->get($testSets[4][0], ['allow_redirects' => true]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[4][1]);

        $response = $client->get($testSets[5][0], ['allow_redirects' => true]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[5][1] );

        //test behavior of changed plugin setting
        $this->setConfig('dontAddSlash', true);

        $response = $client->get($testSets[6][0], ['allow_redirects' => true,]);
        $this->assertSame($response->getEffectiveUrl(), 'http://' . $host . $testSets[6][1]);

        return $testSets;
    }

    /**
     * set plugin config and clear shop cache
     *
     * @param string $name
     * @param bool $value
     * @param bool $sleep
     */
    protected function setConfig($name, $value, $sleep = true)
    {
        Shopware()->Container()->get('config_writer')->save($name, $value, 'ScopRedirecter');
        Shopware()->Container()->get('cache')->clean();
        Shopware()->Container()->get('config')->setShop(Shopware()->Shop());

        \exec('sh ' . __DIR__ . '/../../../../var/cache/clear_cache.sh>/dev/null');
        if ($sleep === true) {
            \sleep(3);
        }
    }
}
