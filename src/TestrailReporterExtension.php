<?php

namespace flexperto\BehatTestrailReporter;


use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class TestrailReporterExtension implements Extension
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container) {
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey() {
        return "testrail_reporter";
    }

    /**
     * @inheritdoc
     */
    public function initialize(ExtensionManager $extensionManager) {
    }

    /**
     * @inheritdoc
     */
    public function configure(ArrayNodeDefinition $builder) {
        $builder->children()->scalarNode("baseUrl")->defaultNull();
        $builder->children()->scalarNode("username")->defaultNull();
        $builder->children()->scalarNode("apiKey")->defaultNull();
        $builder->children()->scalarNode("runId")->defaultNull();
        $builder->children()->scalarNode("testidPrefix")->defaultValue("test_rail_");
        $builder->children()->arrayNode("customFields")->prototype("scalar");
        $builder->children()->booleanNode("enabled")->defaultTrue();
        $builder->children()->booleanNode("createTestRun")->defaultFalse();
        $builder->children()->scalarNode("projectId")->defaultNull();
    }

    /**
     * @inheritdoc
     */
    public function load(ContainerBuilder $container, array $config) {
        $willCreateTestRun = $config['createTestRun'] && $config['projectId'] !== NULL;
        $willHaveRunId = $config['runId'] !== NULL || $willCreateTestRun;

        if (
            $config['enabled'] &&
            $config['baseUrl'] !== NULL &&
            $config['username'] !== NULL &&
            $config['apiKey'] !== NULL &&
            $willHaveRunId
        ) {
            $definition = new Definition("flexperto\\BehatTestrailReporter\\testrail\\TestrailReporter");
            $definition->addArgument($config['baseUrl']);
            $definition->addArgument($config['username']);
            $definition->addArgument($config['apiKey']);
            $definition->addArgument($config['testidPrefix']);
            $definition->addArgument($config['customFields']);
            $definition->addArgument($config['runId']);
            $definition->addArgument($config['projectId']);

            $container->setDefinition("testrail.reporter", $definition)->addTag('event_dispatcher.subscriber');
        }
    }

}