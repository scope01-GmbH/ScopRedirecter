<?php

use ScopRedirecter\Models\Redirecter;
use Shopware\Components\CSRFWhitelistAware;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Models\Shop\Locale;

class Shopware_Controllers_Backend_ScopRedirecter extends \Shopware_Controllers_Backend_Application implements CSRFWhitelistAware
{
    protected $model = Redirecter::class;
    protected $alias = 'redirecter';

    /**
     * Adds the action to CSRF Whitelist
     *
     * @return array|string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'export',
        ];
    }


    /**
     * Action for creating a new redirect
     *
     * @param Locale $locale
     * @throws Exception
     */
    public function createAction()
    {

        $createSuccess = $this->container->get('snippets')
            ->getNamespace('backend/scop_redirecter/messages/messages')
            ->get('create_success', 'Please fill in all red fields');

        $missingFields = $this->container->get('snippets')
            ->getNamespace('backend/scop_redirecter/messages/messages')
            ->get('missing_fields', 'Please fill in all red fields');

        $redirectExists = $this->container->get('snippets')
            ->getNamespace('backend/scop_redirecter/messages/messages')
            ->get('redirect_exists', 'Please fill in all red fields');

        $dbalConnection = $this->get('dbal_connection');

        $startUrl = $this->Request()->getParam('startUrl');
        $targetUrl = $this->Request()->getParam('targetUrl');
        $httpCode = $this->Request()->getParam('httpCode');

        $queryBuilder = $dbalConnection->createQueryBuilder();
        $queryBuilder->select('*')
            ->from('scop_redirecter')
            ->where('start_url = "' . $startUrl . '"')
            ->setMaxResults(1);;
        $data = $queryBuilder->execute()->fetchAll();

        //check if start url already exists
        if(count($data) > 0){
            $this->View()->assign(["success" => false,
                "error" => $redirectExists,
                "error_message" => $redirectExists,
                "message" => $redirectExists]);
        }else{
            //check for empty entries
            if($startUrl == "" || $targetUrl == "" || $httpCode == ""){
                $this->View()->assign(["success" => false,
                    "error" => $missingFields,
                    "error_message" => $missingFields,
                    "message" => $missingFields]);
            }else {
                parent::createAction();
                $this->View()->assign(["success" => false,
                    "error" => $createSuccess,
                    "error_message" => $createSuccess,
                    "message" => $createSuccess]);
            }
        }
    }

    /**
     * Import Action to import redirects from csv file to DB
     *
     * @throws Exception
     */
    public function importAction()
    {
        $httpRequest = Request::createFromGlobals();
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        if ($this->Request()->files !== null) {
            $file = $this->Request()->files->get('importCsv');
        } else {
            $file = $httpRequest->files->get('importCsv');
        }

        if(!file_exists($file))
        {
            $this->View()->assign(['success' => false, 'data' => ["No valid csv file given"]]);
        }elseif(!mb_check_encoding(file_get_contents($file), 'UTF-8'))
        {
            $this->View()->assign(['success' => false, 'data' => ["File must be UTF-8 formatted"]]);
        }else
        {
            $path = $file->getPathname();
            $openPath = fopen($path, "r");

            //create multidimensional array from csv => [rows =>[entries]]
            $csvSetArray = array();
            while ($line = fgetcsv($openPath)) {
                $csvSetArray[] = explode(";", $line[0]);
            }
            $rowCount = count($csvSetArray);

            //insert data to DB
            for($i=0; $i<$rowCount; $i++)
            {
                $queryBuilder
                    ->insert('scop_redirecter')
                    ->values(['start_url' => '?', 'target_url' => '?', 'http_code' => '?',])
                    ->setParameters([
                        0 => $csvSetArray[$i][0],
                        1 => $csvSetArray[$i][1],
                        2 => $csvSetArray[$i][2]
                    ]);
                try{
                    $queryBuilder->execute();
                }catch (Exception $e){
                    //skip and go on
                }
            }
            $this->View()->assign(['success' => true, 'data' => []]);
        }
    }

    /**
     * Export action to Export redirects in DB to csv and send as response
     *
     * @throws Exception
     */
    public function exportAction()
    {

        //setting headers for HTTP Response
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $this->Response()->setHeader('content-type', 'text/csv; charset=utf-8');
        $this->Response()->setHeader('content-disposition', 'attachment;filename=redirects.csv');
        echo "\xEF\xBB\xBF";


        $dbalConnection = $this->get('dbal_connection');
        //query data
        $queryBuilder = $dbalConnection->createQueryBuilder();
        $queryBuilder->select('*')
            ->from('scop_redirecter');
        $data = $queryBuilder->execute()->fetchAll();


        //create file and write rows to it
        $file = fopen('php://output', 'w');
        foreach ($data as $line) {
            fputcsv($file, $line, ';');
        }
        fclose($file);

    }
}
