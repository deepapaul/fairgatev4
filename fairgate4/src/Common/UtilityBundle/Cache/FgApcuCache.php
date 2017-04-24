<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Common\UtilityBundle\Cache;

use Doctrine\Common\Cache\ApcuCache;

/**
 * APCu cache provider.
 *
 * @link   www.doctrine-project.org
 * @since  1.6
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class FgApcuCache extends ApcuCache {

    /**
     * Delete cache entries where the key has the passed prefix
     *
     * @param string $prefix
     *
     */
    public function deleteByPrefix($prefix) {
        apcu_delete(new \APCUIterator("/$prefix/"));
    }

    /**
     * Delete cache entries where the key has the passed prefix
     *
     * @param int     $cacheKey      Cachekey used for caching
     * @param Integer $cacheLifeTime Cache expiry time
     *
     * @return the cached result based on the setting. If cacheing is not anabled, it will return the data from DB.
     */
    public function getCachedResult($query, $cacheKey, $cacheLifeTime, $cachingEnabled) {
        if ($cachingEnabled) {
            return $query->getQuery()
                            ->useResultCache(true, $cacheLifeTime, $cacheKey)
                            ->getResult();
        } else {
            return $query->getQuery()
                            ->getResult();
        }
    }

    /**
     * Set the prefix value and call the function deleteByPrefix
     *
     * @param int     $clubCacheKey     Cachekey used for caching
     * @param String  $prefixName       Prefix used for caching query
     * @param String  $trailingPrefix   Trailing Prefix used for caching query
     *
     */
    public function setPrefixValueForDelete($clubCacheKey, $prefixName, $trailingPrefix = '') {
        $cacheKey = str_replace('{{cache_area}}', $prefixName, $clubCacheKey);
        if($trailingPrefix != ''){
            $cacheKey = $cacheKey . $trailingPrefix;
        }
        $this->deleteByPrefix($cacheKey);
    }
    
    /**
     * Use this function as a wrapper for deleting any apc data using ID
     *
     * @param int     $clubCacheKey Cachekey used for caching
     * @param String  $prefixName   Prefix used for caching query
     *
     */
    public function deleteCacheById($clubCacheKey, $cacheArea, $cachePrefix) {
        $prefixName = str_replace('{{cache_area}}',$cacheArea, $clubCacheKey);
        $cacheDeleteId = $prefixName.$cachePrefix;        
        $this->delete($cacheDeleteId);
    }

}
