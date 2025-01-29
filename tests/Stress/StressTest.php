<?php

namespace App\Tests\Stress;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class StressTest extends KernelTestCase
{
    public function testLoadTestTransactions(): void
    {
        $kernel = self::bootKernel();

        // Create the application
        $application = new Application($kernel);

        // Find the command
        $command = $application->find('app:load-test-transactions');

        // Create a tester for the command
        $commandTester = new CommandTester($command);

        // Execute the command with verbose output
        $commandTester->execute([], ['verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE]);

        // Get the output
        $output = $commandTester->getDisplay();

        // Debugging: Print the output to see what is being captured
        fwrite(STDERR, "Command Output:\n$output\n");

        // Assertions
        $this->assertStringContainsString('Starting load test for', $output, 'The output should indicate the start of the load test.');
        $this->assertStringContainsString('transactions succeeded.', $output, 'The output should indicate the number of successful transactions.');

        // Ensure the time limit was not exceeded
        $this->assertStringNotContainsString('Time limit exceeded', $output, 'The load test should not exceed the time limit.');

        // Optionally check for success code
        $this->assertEquals(0, $commandTester->getStatusCode(), 'The command should exit with a status code of 0.');
    }
}
