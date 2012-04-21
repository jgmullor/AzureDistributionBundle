<?php
/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace WindowsAzure\DistributionBundle\Deployment\Assets;

interface Strategy
{
    /**
     * Deploy the assets of a deployment before packaging is started.
     *
     * The selected strategy depends on the configuration of the site and could
     * be either local to the web role or blob storage for example.
     */
    function deploy();
}

