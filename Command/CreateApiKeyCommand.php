<?php

namespace AW\HmacBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AW\HmacBundle\Entity\ApiUser as ApiUser;

/**
 * API Key creation
 *
 * @category  Commands
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class CreateApiKeyCommand extends ContainerAwareCommand
{
    /**
     * (non-PHPdoc)
     * 
     * @see \Symfony\Component\Console\Command\Command::configure()
     * 
     * @return void
     */
    protected function configure()
    {
        $this->setName('aw:apikey:create')
            ->setDescription('Creates a new API Key')
            ->setDefinition(
                array(
                    new InputArgument(
                        'apikey', 
                        InputArgument::REQUIRED, 
                        'The API Key'
                    ),
                    new InputArgument(
                        'email', 
                        InputArgument::REQUIRED, 
                        'The email address associated with the key'
                    )
                )
            )->setHelp(<<<EOT
The <info>tocc:apikey:create</info> command creates a new API key
EOT
            );
    }

    /**
     * (non-PHPdoc)
     * 
     * @param Symfony\Component\Console\Input\InputInterface  $input  In
     * @param Symfony\Component\Console\Input\OutputInterface $output Out
     * 
     * @see \Symfony\Component\Console\Command\Command::execute()
     * 
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $apikey = $input->getArgument('apikey');
            $email = $input->getArgument('email');

            $em = $this->getContainer()->get('doctrine')->getManager();
            $userService = $this->getContainer()->get(
                'AW_apiuser_service'
            );
            
            $user = $userService->createUser($apikey, $email);
            $user->addRole('ADMIN');
            
            $em->persist($user);
            $em->flush();

            $output->writeln('Created new API key');
            $output->writeln(
                sprintf('- Key:    <comment>%s</comment>', $apikey)
            );
            $output->writeln(
                sprintf('- Secret: <comment>%s</comment>', $user->getApisecret())
            );
            
            
        } catch (\Exception $ex) {
            $output->writeln(
                sprintf(
                    '<comment>%s</comment>',
                    $ex->getMessage()
                )
            );
        }
    }
}