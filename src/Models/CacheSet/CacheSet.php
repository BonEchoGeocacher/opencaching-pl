<?php

namespace src\Models\CacheSet;


use src\Models\Coordinates\Coordinates;
use src\Utils\Database\OcDb;
use src\Utils\Database\QueryBuilder;
use src\Models\GeoCache\GeoCache;
use src\Models\User\User;
use src\Models\Coordinates\NutsLocation;
use src\Utils\Text\Formatter;
use src\Utils\Debug\Debug;
use src\Utils\Uri\Uri;
use src\Models\OcConfig\OcConfig;

class CacheSet extends CacheSetCommon
{
    // This is path in DynBaseDir and in url a well
    const DIR_LOGO_IMG = '/images/uploads/geopaths/logos';

    private $id;
    private $uuid;
    private $name;
    private $image;
    private $type;
    private $centerCoordinates;
    private $status;
    private $dateCreated;
    private $cacheCount;
    private $description;
    private $perccentRequired;
    private $conquestedCount;

    /** @var NutsLocation */
    private $location = null;

    /** @var array of CacheSetOwner */
    private $owners = null;

    public function __construct()
    {
        parent::__construct();
    }

    public static function fromCacheSetIdFactory($id)
    {
        $cs = new self();
        $cs->id = $id;

        if( $cs->loadDataFromDb() ){
            return $cs;
        }

        return null;
    }

    private function loadDataFromDb($fields = null)
    {
        if (is_null($fields)) {
            $fields = "*"; // default select all fields
        }

        $s = $this->db->multiVariableQuery(
            "SELECT $fields FROM PowerTrail WHERE id=:1 LIMIT 1", $this->id);

        if($row = $this->db->dbResultFetch($s)){
            $this->loadFromDbRow($row);
            return true;
        }
        return false;
    }

    private function loadFromDbRow(array $dbRow)
    {
        foreach ($dbRow as $key => $val) {
            switch ($key) {
                case 'id':
                    $this->id = (int) $val;
                    break;
                case 'name':
                    $this->name = $val;
                    break;
                case 'image':
                    if($val === ''){
                        /* no image was loaded by user, set default image */
                        $val = '/images/blue/powerTrailGenericLogo.png';
                    }
                    $this->image = $val;
                    break;
                case 'type':
                    $this->type = (int) $val;
                    break;
                case 'status':
                    $this->status = (int) $val;
                    break;
                case 'dateCreated':
                    $this->dateCreated = new \DateTime($val);
                    break;
                case 'cacheCount':
                    $this->cacheCount = (int) $val;
                    break;
                case 'description':
                    $this->description = $val;
                    break;
                case 'perccentRequired':
                    $this->perccentRequired = $val;
                    break;
                case 'conquestedCount':
                    $this->conquestedCount = $val;
                    break;
                case 'points':
                    $this->points = $val;
                    break;
                case 'uuid':
                    $this->uuid = $val;
                    break;

                case 'centerLatitude':
                case 'centerLongitude':
                    // cords are handled below...
                    break;
                default:
                    Debug::errorLog("Unknown column: $key");
            }
        }

        // and the coordinates..
        if (isset($dbRow['centerLatitude'], $dbRow['centerLongitude'])) {
            $this->centerCoordinates = Coordinates::FromCoordsFactory(
                $dbRow['centerLatitude'], $dbRow['centerLongitude']);
        }
    }

    private static function FromDbRowFactory(array $dbRow)
    {
        $gp = new self();
        $gp->loadFromDbRow($dbRow);
        return $gp;
    }

    /**
     * Returns list of all cache-sets
     * @return array
     */
    public static function GetAllCacheSets($statusIn=array(), $offset=null, $limit=null)
    {

        if(empty($statusIn)){
            $statusIn = array(CacheSetCommon::STATUS_OPEN);
        }


        $query = QueryBuilder::instance()
            ->select("*")
            ->from("PowerTrail")
            ->where()
                ->in("status", $statusIn)
            ->limit($limit, $offset)
            ->build();

        $stmt = self::db()->simpleQuery($query);

        return self::db()->dbFetchAllAsObjects($stmt, function ($row){
            return self::FromDbRowFactory($row);
        });
    }

    public static function GetAllCacheSetsCount(array $statusIn = array())
    {

        $query = QueryBuilder::instance()
            ->select("COUNT(*)")
            ->from("PowerTrail")
            ->where()
                ->in("status", $statusIn)
            ->build();

        return self::db()->simpleQueryValue($query,0);
    }

    /**
     * Returns CacheSet[] of open GeoPaths with names containing $name.
     * If null $name given - all open GeoPaths will be returned
     *
     * @param string $name
     * @return CacheSet[]|NULL
     */
    public static function getCacheSetsByName($name = null)
    {
        if (is_null($name) || !is_string($name)) {
            $query = "SELECT * FROM `PowerTrail` WHERE `status` = :1 ORDER BY `name`";
            $stmt = self::db()->multiVariableQuery($query, CacheSet::STATUS_OPEN);
        } else {
            $query = "SELECT * FROM `PowerTrail` WHERE `status` = :1 AND `name` LIKE :2 ORDER BY `name`";
            $stmt = self::db()->multiVariableQuery($query, CacheSet::STATUS_OPEN, '%' . $name . '%');
        }
        return self::db()->dbFetchAllAsObjects($stmt, function ($row){
            return self::FromDbRowFactory($row);
        });
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getIcon()
    {
        return self::GetTypeIcon($this->type);
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getTypeTranslation()
    {
        return tr(self::GetTypeTranslationKey($this->type));
    }

    public function getStatusTranslation()
    {
        return tr(self::GetStatusTranslationKey($this->status));
    }

    public function getCreationDate($formatted = false)
    {
        if ($formatted) {
            return Formatter::date($this->dateCreated);
        } else {
            return $this->dateCreated;
        }
    }

    public function getCacheCount()
    {
        return $this->cacheCount;
    }

    public function getGainedCount()
    {
        return $this->conquestedCount;
    }

    /**
     *
     * @return NULL|\src\Models\Coordinates\Coordinates
     */
    public function getCoordinates()
    {
        return $this->centerCoordinates;
    }

    /**
     *
     * @return \src\Models\Coordinates\NutsLocation
     */
    public function getLocation()
    {
        if(!$this->location){
            $this->location = NutsLocation::fromCoordsFactory($this->centerCoordinates);
        }

        return $this->location;
    }

    public function getUrl()
    {
        return self::getCacheSetUrlById($this->id);
    }

    public function getOwners()
    {
        if(!$this->owners){
            $arr = CacheSetOwner::getOwnersOfCacheSets([$this->getId()]);
            $this->owners = $arr[$this->getId()];
        }

        return $this->owners;
    }

    /**
     * Set array of CacheSetOwners as cacheSet owners
     *
     * @param array $owners
     */
    public function setOwners(array $owners)
    {
        $this->owners = $owners;
    }

    /**
     * Return all cacheSets which has less active caches than required ratio for completion
     * @return array
     */
    public static function getCacheSetsToArchive()
    {
        $db = self::db();
        $rs = $db->simpleQuery(
            "SELECT * FROM (
                SELECT pt.id, pt.type, pt.name, pt.cacheCount,
                    pt.centerLongitude, pt.centerLatitude,
                    COUNT(*) AS activeCaches,
                    100*COUNT(*)/pt.cacheCount AS currentRatio,
                    pt.perccentRequired AS ratioRequired
                FROM PowerTrail AS pt
                JOIN powerTrail_caches AS ptc ON ptc.PowerTrailId = pt.id
                JOIN caches AS c ON ptc.cacheId = c.cache_id
                WHERE pt.status = ".CacheSet::STATUS_OPEN."
                    AND c.status = ".GeoCache::STATUS_READY."
                GROUP BY pt.id
            ) AS allPts WHERE currentRatio < ratioRequired");

        return $db->dbResultFetchAll($rs, OcDb::FETCH_ASSOC);
    }

    public static function getLastCreatedSets($limit)
    {

        $db = self::db();

        $limit = $db->quoteLimit($limit);

        $rs = $db->multiVariableQuery(
            "SELECT id, name, centerLatitude, centerLongitude,
                    status, dateCreated, cacheCount, image
            FROM PowerTrail
            WHERE status = :1
            ORDER BY dateCreated DESC
            LIMIT $limit", self::STATUS_OPEN);

        $result=[];
        while($row = $db->dbResultFetch($rs, OcDb::FETCH_ASSOC)){
            $result[] = self::FromDbRowFactory($row);
        }

        return $result;
    }

    public static function getActiveCacheSetsCount()
    {
        return self::db()->multiVariableQueryValue(
            'SELECT COUNT(*) FROM PowerTrail WHERE status = :1',
            0,self::STATUS_OPEN);
    }

    public function prepareForSerialization()
    {
        parent::prepareForSerialization();
        array_walk($this->owners, function (&$element, $key){
            $element->prepareForSerialization();
        });
    }

    public function restoreAfterSerialization()
    {
        parent::restoreAfterSerialization();
    }

    /**
     * Returns true if given user is an owner of this geopath
     */
    public function isOwner(User $user)
    {
        foreach($this->getOwners() as $owner) {
            if($owner->getUserId() == $user->getUserId()) {
                return true;
            }
        }
        // there is no such user on the list of owners
        return false;
    }

    /**
     * Update geopath logo
     * @param string $newLogoUrl
     */
    public function updateLogoImg($newLogoUrl)
    {
        // old logo url contained also hostname etc.
        $oldLogo = Uri::getPathfromUrl($this->image);

        // update logo in DB
        $this->db->multiVariableQuery(
            'UPDATE PowerTrail SET image=:1 WHERE id = :2',
            $newLogoUrl, $this->id);

        $this->image = $newLogoUrl;

        if($oldLogo != $newLogoUrl){
            // delete old logo
            if (is_file(OcConfig::getDynFilesPath().$oldLogo)) {
                unlink (OcConfig::getDynFilesPath().$oldLogo);
            }
        }
    }
}
