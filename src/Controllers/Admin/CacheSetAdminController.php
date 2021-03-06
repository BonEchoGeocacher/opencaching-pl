<?php
namespace src\Controllers\Admin;

use src\Controllers\BaseController;
use src\Utils\Uri\Uri;
use src\Models\CacheSet\CacheSet;
use src\Models\ChunkModels\ListOfCaches\Column_CacheSetNameAndIcon;
use src\Models\ChunkModels\ListOfCaches\ListOfCachesModel;
use src\Models\ChunkModels\ListOfCaches\Column_SimpleText;
use src\Models\ChunkModels\DynamicMap\DynamicMapModel;
use src\Models\ChunkModels\DynamicMap\CacheSetMarkerModel;

class CacheSetAdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        // this controller is only for Admins
        $this->redirectNotLoggedUsers();

        /* !!!temporary disabled for tests:

        if(!$this->loggedUser->hasOcTeamRole()){
            $this->view->redirect('/');
            exit;
        }
        */

    }

    public function isCallableFromRouter($actionName)
    {
        // all public methods can be called by router
        return true;
    }

    public function index()
    {

    }

    /**
     * Display list of cacheSets(geopaths) which should be archived because
     * the number of active caches is lower than number requested for completion.
     */
    public function cacheSetsToArchive()
    {
        $this->view->setTemplate('admin/cacheSet/cacheSetsToArchive');
        $this->view->addLocalCss(
            Uri::getLinkWithModificationTime(
                '/views/admin/cacheSet/cacheSetsToArchive.css'));

        $this->view->loadJQuery();

        $csToArchive = CacheSet::getCacheSetsToArchive();

        if( empty($csToArchive)){
            $this->view->setVar('noCsToArchive', true);
            $this->view->buildView();
            exit;
        } else {
            $this->view->setVar('noCsToArchive', false);
        }

        // prepare model for list of watched caches
        $listModel = new ListOfCachesModel();
        $listModel->addColumn(
            new Column_CacheSetNameAndIcon( tr('admCs_cacheSet'),
                function($row){
                    return [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'name' => $row['name']
                    ];
                }));
        $listModel->addColumn(
            new Column_SimpleText( tr('admCs_currentRatio'), function($row){
                return $row['activeCaches'] . ' ( '. round($row['currentRatio']).'% ) ';
            }));

        $listModel->addColumn(
            new Column_SimpleText( tr('admCs_requiredRatio'), function($row){
                // find number of required caches
                $requiredCachesNum = ceil($row['cacheCount']*$row['ratioRequired']/100);
                return $requiredCachesNum . ' ( '. round($row['ratioRequired']).'% )';
            }));

        $listModel->addDataRows($csToArchive);
        $this->view->setVar('listOfCssToArchiveModel', $listModel);



        // init map-chunk model
        $this->view->addHeaderChunk('openLayers5');

        $mapModel = new DynamicMapModel();
        $mapModel->addMarkersWithExtractor(CacheSetMarkerModel::class, $csToArchive, function($row){

            $ratioTxt = round($row['currentRatio']).'/'.$row['ratioRequired'].'%';

            $marker = new CacheSetMarkerModel();

            $marker->lon = $row['centerLongitude'];
            $marker->lat = $row['centerLatitude'];
            $marker->icon = CacheSet::GetTypeIcon($row['type']);

            $marker->link = CacheSet::getCacheSetUrlById($row['id']);
            $marker->name = $row['name']." ($ratioTxt)";
            return $marker;
        });

        $this->view->setVar('mapModel', $mapModel);

        $this->view->buildView();

    }
}
