<?php

namespace App\Util;

use Doctrine\Common\Collections\ArrayCollection;

class ServiceStatusEngine
{
    private const string STATUS_TOOL_VERSION = '1.0.4';

    /**
     * @var ArrayCollection<array-key, ServiceStatus> $services
     */
    private ArrayCollection $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function add(ServiceStatus $serviceStatus): void
    {
        $this->services->add($serviceStatus);
    }

    public function getStatusArray(): array
    {
        $status = [
            'overallStatus' => $this->areAllServicesOk() ? ServiceStatus::STATUS_OK : ServiceStatus::STATUS_ERROR,
            'version' => self::STATUS_TOOL_VERSION,
            'timestamp' => (new \DateTime())->format('c'),
        ];

        $services = [];
        foreach ($this->services as $service) {
            $services[$service->getName()] = $service->getResults();
        }
        $status['services'] = $services;
        return $status;
    }

    public function areAllServicesOk(): bool
    {
        return 0 === $this->services->filter(function (ServiceStatus $serviceStatus) {
            return ServiceStatus::STATUS_ERROR === $serviceStatus->getStatus();
        })->count();
    }
}
