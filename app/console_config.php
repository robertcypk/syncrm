<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Doctrine\ORM\Tools\Console\ConsoleRunner,
    Doctrine\ORM\EntityManager;

$console = new Application('Synchro', '1.0');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console->setDispatcher($app['dispatcher']);
/*
$console->register('controller')->setDefinition(array())
    ->setDescription('Make Service Controller')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        return $input;
    });
*/
$console->setHelperSet(new Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($app["db"]),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($app["orm.em"])
)));

$console->addCommands(array(

  new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand,
  new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand,
  new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand,
  new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand,
  new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand,
  new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand,
  new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand,
  new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand,
  new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand,
  new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand,
  new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand,
  new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand,
  new \Doctrine\ORM\Tools\Console\Command\InfoCommand,
  new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand,
  new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand,
  new \Doctrine\DBAL\Tools\Console\Command\ImportCommand,
  new \Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand,
  new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand,
  //new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle()
  //new Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand
));


return $console;
