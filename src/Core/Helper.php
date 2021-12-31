<?php

/**
 * Kotori.php
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015-2022 kokororin. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Helper Class
 *
 * Common API Helpers.
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Composer\Autoload\ClassLoader;
use Kotori\Debug\Hook;
use Kotori\Exception\NotFoundException;
use ReflectionClass;

abstract class Helper
{
    /**
     * Require Array
     *
     * @var array
     */
    protected static $require = [];

    /**
     * Include One File
     *
     * @param  string $path
     * @return boolean
     */
    public static function import($path)
    {
        $path = realpath($path);
        Hook::listen(str_replace(Container::get('config')->get('app_full_path'), '', $path));
        if (!isset(self::$require[$path])) {
            if (self::isFile($path)) {
                require $path;
                self::$require[$path] = true;
            } else {
                self::$require[$path] = false;
            }
        }

        return self::$require[$path];
    }

    /**
     * Detect whether file is existed
     *
     * @param  string $path
     * @return boolean
     */
    public static function isFile($path)
    {
        if (is_file($path)) {
            if (strstr(PHP_OS, 'WIN')) {
                if (basename(realpath($path)) != basename($path)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Global autoload function
     *
     * @param  string $class
     * @return void
     */
    public static function autoload($class)
    {
        $baseRoot = Container::get('config')->get('app_full_path');
        // project-specific namespace prefix
        $prefix = Container::get('config')->get('namespace_prefix');

        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // no, move to the next registered autoloader
            return;
        }

        // get the relative class name
        $relativeClass = substr($class, $len);

        // replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = $baseRoot . '/' . str_replace('\\', '/', $relativeClass) . '.php';

        self::import($file);
    }

    /**
     * recursively create a long directory path
     *
     * @param  string   $pathname
     * @param  int      $mode
     * @return boolean
     */
    public static function mkdirs($pathname, $mode = 0755)
    {
        is_dir(dirname($pathname)) || self::mkdirs(dirname($pathname), $mode);
        return is_dir($pathname) || @mkdir($pathname, $mode); // @codingStandardsIgnoreLine
    }

    /**
     * Get vendor absolute path
     *
     * @return string
     *
     * @throws \Kotori\Exception\NotFoundException
     */
    public static function getComposerVendorPath()
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        $vendorDir = dirname(dirname($reflection->getFileName()));
        if (!$vendorDir) {
            throw new NotFoundException('cannot find composer vendor path');
        }

        return $vendorDir;
    }

    /**
     * Show Kotori Logo
     *
     * @return string
     */
    public static function logo()
    {
        return 'data:image/png;base64,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';
    }

    /**
     * Show Kotori Comment
     *
     * @return string
     */
    public static function comment()
    {
        return '<!--
>>> Powered by Kotori.php <<<
(https://github.com/kokororin/Kotori.php)
                                         iiiiiii
                                  iiiiii        i
                               iiiiii       i    i   iiiii
                              ii    ii       i iii  i     i     i
                              iiii           i iiii       iiii       iii
                             iirirri          iini      i               iiii
                             irniirnri        iii     i                    iiii
                             iiiiiiiiriiii    ir    i  iiiiii i         i     iii
                            i              i       i iriiiiiiiiiii     iiii   iiiii
                            i               i   iiiiri          i i i i   iiii iriiii
                            i               ii iii iiiiiii         iii      iii  rriiiii
                            rr            iiiii       iiiirii iii    iiii     iri irrii iii
                          r ii          ii    i i i ii   iiiiiirriiii iiii     ii iiiii iiii
                          riiini      iiiii iiiiii ii iii iiiiiiiiiirrriiiiri    iiii ri    ii
                         riirrrr    iriiiiiiiiiiiiiiii iii i   iiii iirnrriiiri   iiiiir iii iii
                         rirrrrn   iiiiiiii iii i iiiiiiiii i i irii  iiririiirii  iri ii ii  iii
                        iirrrrrni riiiiiiiiiii  iriiiiiiiiiiiiii iriii iii rriinri  ii iii i i iii
                        irrrrrrnoiiiiiiii i   irri i  i i i iii i iriii  ii irirrri ii  ii  i i iii
                       iirrrrrnni            iniii i             i iiii   ii irrirni ii  iii i i iii
                       irrrrrnn             rni i i  i             ii ii  i   iriirriii iiiiiii   iii
                      iirrrrrn             nni  i i ii              i ii   ii  ii inr ii iii i i i ii
                      iirrrrn           i nni    ii i                  i    i   i irriiii  ii       ii
                      irrrrni i        rinni   i ii i       i          i           iriii   ii      i ii
                       rrrni ii       inrni    iiriii       ii         ii    i      iii     i  iiii  ii
                       rrrr ini iiiiiirnni     iiriri ii i  riii     i ii     i      i      iiiiii  iii
                       rrniinriiiiiiiinnr  iii  rirriir iiiiriri iii i ii i   i             iii    iiii
                       nrnirniiiiiiiirnr        r riirriiiiniirr iii iiiiii   ii            ii    iiiii
                       rrrinniiiiiiiinn         i i irniiiinr nriiiiiiririr   rr            ii  iiriiir
                       rrrrnriiiiiiiio           i   ririiiii rriiiiiinirir i in            ii iiiri ir
                       rrinnriniiiiirr                           iiiiinrirnii iri i i        iiiiri  rr
                       rirnniiniiiiir    nkkmri                        i rri irnii iii i  i  iirri  ini
                       rrrnrrrniiiiir  kkoiikkkn                          iriirni iiiii  ii iirri   rri
                       rrnrrrrniiiiii kk      kk               nkkkkmoi    iinnniiiiiiii riirii    rrr
                       irnrrnrniiiiiink     iioo              kmni  imkkr    irri riiii iriii    irrir
                       iinrrnrnriiiiiin mkinkkrk             ii        nmki    r iri i  rr i    inrrii
                       iinrrnnnniiiir i kmi i  m                i     irnmkr   i iriii iniii  iinrirrr
                       rinrrrnnniiiir   ikr   rr                nki rkmriomkn iiiriii irriii irrriirin
                      iiirnrrrnnniiir    rmrinn                 imi  ii  onnkii rri   rriinirrriiirirni
                      iiirnrrnnnnniii                            koi    ioiniiiirri  rrriirirriiiirirrr
                      riirnrrnnnnonii                             rriinmm    iirri  rrrrr  rriiiiirirrri
                     iriirrrrnrnoiinii                                      riirri rnrni i iriiiirriiriri
                    iiiinirnnrrnr inri                                   rr  nriiirnni  i  riiiirriirrir
                    iiiiirirnnrrnr  iiri                                 i   nrriinnii     irriiiriiirriir
                    riiiriirnrnrnr  i   i                                  ionri rn   i    riiriiiiiirri ir
                   iiiiiiiinnrnnnn  ir                                   inori  ir   i    rririiiriiirrii ii
                  iriiiiiiinrrrrnr  im          nkktnriii               iii     ii      iiririi iriiiiriri ii
                  irriiiiiirnrninnr  rmr         nknrirrrrnnr                         iri iirrriirriiiirrri  ii
                 rriiiiiiinrniinor  nrmr        in        ir                 ini  iinni  irrri rrrrrrirrrni  ir
                rrriiiiiirnnn itni innrin        ii       ii                ioonnnnnni  iinri ii iiirrirrriri ir
               inii iiiiinrni rnoi rrni rni       iiiiiiiii               iri rnrrrnr i irri ii      irirr iri ir
              itiiiiiiiiinnn  rto  nro  inir                            irii   rnrrn ii iniii          nrri iri iri
             ioiii  iiiinroi  rnn irrr i riirr                      iiiii i i   irri i iriii            irn   iriiri
            imi ii   iiinnr   non rini i riiinmr                iiiiiiiiii i   i  ri i rri               rnr   iri ii
           imriir     irnn ii nmi nin ii iriiimiirii      i iiiiiii iiiiiii i ii ii i ini                rini    iiiiri
           or  ii     inoiiiiinmiiririiiiiniiitri  iiiiiiinr iiiiiiiiiiiii   iii rii ini                 iirr  ii  iiiiiii
          ni   ii  i  rki     mo iriiiiiiiniiitri    ii  iooi iiiiiiiii i   iii ii  in                    riri ii i iiiiii
         rr    r  ii ion      mr iri      riiitr     i  ionnoi iiiiiii i   ii   ii ini                   iiriri iiii  iiiii
        io    ir ir  rk       ki rn       ri ikr       ronnntr  iii i i   ii   ii ir      i          i iriiii ri iiiiiiiiirri
        m     iiini  ki      ik  nn       ii  ti      imnntiir i i   i   i     i ii      i              rriiii ii iiiiiii  irri
       mr     rirr  nn       oo  nr        i  ni     ionnni ii iiiii    i     i  i     ii                rriiii iiiiiiiiiii iirr
      nm      rini ik        ki  ni        ri ii    ionnoi   iii   ii  ii    ii ii   ii                   iririi iiiiiiiiiiii  irn
     ik       rini ni       it   ni         iiii   ionttiii ii      ii ii   ii ii   i                      rrirr  iiiiiiiiiiiii  rrr
     kn       rnriin        ni   ni        i iirirntnoni  iri       iiiii   i  i   i                        rrirr  irrrrriiiiiiiiiiirni
    nmi       nnr n        in    rr        i  iinnntnni    ii   ii    i    i  r i                           iriiri  iiiiriiiiiiiirriirni
   ioo        nniir        k     in  i     i  i   rnnni     i    i   iii  ii ri                              irrini               iiirinr
   ron        onir        or      ki  i    i     nnnnii     i    iriii    i ir                                riiini  i              irirni
   mtn        nnir       no       ro        i   nonr ii     iiiiii iii   i  r                                 iririni  i              iiiini
  iotr        nnii      rm         nn       iinnnnr   iriii          i  ii ii                                 irrirrnr  i              iiiinr
  itnr   i i  nnii     nm           inri   irnnnni     i              iii ii                                   riiirinri ii            iiiiini
   nnr        iti     mm          i   inkmkmknnii                      i  r                                    iriririnni ii            iiiiini
   nnn         nr   rkt                      rii                      ii ni                                    iirriirirni iii           iiiirr
   rnn         rriimkr                       riiii                   ii nr          i                          iriirrirrrnniiii           iiiini
    nki        irnmo                        r ii iiiiiii          iiii rn           i                          irriirririrrniiiii          iiirn
     oo         rin           i            ii   i               iii iirnr           iii                         rrriiirrrrrrnniiiri        irirri
      on        rii                        i     iii      iiiiii    irnr            iiii                        rrrriiirrrrrrrriiiiri       iirnr
       rn        rir                      r        iiiiiiii        iirri        ii  iii                         rrrirriiirrrrrrri irrii       rnni
         i       iiiii          ii       ii           i           iiinri         iii iii                        rnirrrrriiinrrrrni  irrr      inrri
                  ii iii                 i                       ir irr           iiiii                         rnrrrrrrrriiirrrrnn   iinri   inrn
                   iniiiri       i       i                      iri nrr            iiiii                         rnrrrrrrrriiiinrrrni    irnr inrrii
                     iiiiiiiiiirni       i                      ni inri              iiii                        inrnrrrrrrrriiiirrrnni     irnnni  i
                        iiiiiiii   i     i                     nr  rrri               iiiii                       rrrrrnrrrrrrri iirrnnn       inn
-->
';
    }
}
