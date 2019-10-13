<?php
/**
 * Created by PhpStorm.
 * User: ENGENEER
 * Date: 7/3/2018
 * Time: 12:52 PM
 */

namespace common\modules\drole\models\gate;


class PrivateObjectsDataHandler
{
    public static $strictServiceObjects = ['97086af0-956b-4380-a385-ea823cff377a',
        '2ed029b6-d745-4f85-8d9f-2dccd2a7da37', '3db2f640-e01a-42ac-904e-87a46e0373fd',
        '5cb705ea-6c8c-4dae-a620-248545acab14', '03835abe-c4f8-4449-8aa1-ff624838195e'];
    public static $strictPrivateObjects = ['7052a1e5-8d00-43fd-8f57-f2e4de0c8b24'];
    public static $relativePrivateObjects = ['2ed029b6-d745-4f85-8d9f-2dccd2a7da37',
        '3db2f640-e01a-42ac-904e-87a46e0373fd', 'fd27729c-0f30-444b-a124-e3e16069e7d0',
        '8814bc5a-210d-42e3-805d-3d2d5787942c', '7595cf2c-60b6-400d-934c-ec90dad1d66c',
        '5cb705ea-6c8c-4dae-a620-248545acab14', '20e51bea-c4af-4822-8494-4746920ffd70',
        '81f18d1f-def0-4f33-96f1-bfd7a25f8bcd', '62a15104-6ad9-4922-afd0-68e0b57ff87f',
        '655d85fa-2199-40fe-9836-295bf8a8a316', '5c1a5894-f6df-4c96-a84d-6679f3375bb7',
        '9cb00590-997d-43dd-b5b2-a1dabb35f74b', '5712a691-690c-4aa3-b84a-8aacc0e894a4', '6b6703fe-7ace-4e40-b1d3-2fd3dd30f65c'];

    public static function getLevelOfAccess($objectID)
    {
        if (in_array($objectID, self::$strictServiceObjects)) {
            return 0;
        }
        if (in_array($objectID, self::$strictPrivateObjects)) {
            return 1;
        }
        if (!in_array($objectID, self::$relativePrivateObjects)) {
            return 2;
        }
        return 3;
    }

    public static function getLevelOfAccessGetter($objectID)
    {
        /*if($objectID == '5cb705ea-6c8c-4dae-a620-248545acab14'){
            echo "try work with coin :[" . in_array($objectID, self::$relativePrivateObjects) . "]";
        }*/
        if (in_array($objectID, self::$strictServiceObjects)) {
            return 0;
        }
        if (in_array($objectID, self::$strictPrivateObjects)) {
            return 1;
        }
        if (!in_array($objectID, self::$relativePrivateObjects)) {
            return 2;
        }
        return 3;
    }
}