<?php

namespace App;

use Symfony\AI\McpBundle\McpBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable // @phpstan-ignore return.deprecatedInterface
    {
        yield new FrameworkBundle();
        yield new McpBundle();
        yield new MonologBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->services()
            ->load('App\\', __DIR__.'/*')
            ->autowire()
            ->autoconfigure()
        ;

        $container->extension('framework', [
            'mailer' => [
                'dsn' => '%env(default::MAILER_DSN)%',
            ],
        ]);

        $container->extension('mcp', [
            'servers' => [
                'default' => [
                    'transports' => [
                        'stdio' => false,
                    ],
                    'http' => [
                        'path' => '/',
                        'allowed_hosts' => ['*'],
                    ],
                    'registry' => '*',
                ],
            ],
        ]);

        if ('dev' === $this->environment) {
            $container->extension('monolog', [
                'handlers' => [
                    'main' => [
                        'type' => 'stream',
                        'path' => '%kernel.logs_dir%/%kernel.environment%.log',
                        'level' => 'debug',
                        'channels' => ['!event'],
                    ],
                    'console' => [
                        'type' => 'console',
                        'process_psr_3_messages' => false,
                        'channels' => ['!event', '!console'],
                    ],
                ],
            ]);
        }

        if ('prod' === $this->environment) {
            $container->extension('monolog', [
                'handlers' => [
                    'main' => [
                        'type' => 'fingers_crossed',
                        'action_level' => 'error',
                        'handler' => 'nested',
                        'excluded_http_codes' => [404, 405],
                        'channels' => ['!deprecation'],
                        'buffer_size' => 50,
                    ],
                    'nested' => [
                        'type' => 'stream',
                        'path' => 'php://stderr',
                        'level' => 'debug',
                        'formatter' => 'monolog.formatter.json',
                    ],
                ],
            ]);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('.', 'mcp');
    }
}
