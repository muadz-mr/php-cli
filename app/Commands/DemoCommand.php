<?php

namespace App\Commands;

use App\Enums\StringCase;
use LaravelZero\Framework\Commands\Command;

class DemoCommand extends Command
{
    private $inputString;

    private $options;

    private $result;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'demo:run';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run all tasks for demo';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        restart:
        do {
            $this->inputString = $this->ask("Please enter your string");
        } while (!$this->inputString);

        choose:
        $this->options = [
            "Run all",
            "Convert to uppercase",
            "Convert to alternate uppercase and lowercase",
            "Generate CSV file from input"
        ];
        $optionName = $this->choice("Please select an option", $this->options, 0);

        $this->executeChoice($optionName);

        $decisions = ["new", "other", "exit"];
        $decisionName = $this->choice("Do you want to restart with new string, choose other options or exit?", $decisions, 2);

        $decision = array_search($decisionName, $decisions);
        if ($decision == 0) {
            goto restart;
        } elseif ($decision == 1) {
            goto choose;
        } else {
            $this->info("Thank you for using the program.");
            $this->info("Created by Mu'adz MR © 2023.");
            die();
        }
    }

    private function convertToLowercase(string $input): string
    {
        return strtolower($input);
    }

    private function convertToUppercase(string $input): string
    {
        return strtoupper($input);
    }

    private function convertToAlternateCases(string $input): string
    {
        $output = "";
        $currentCase = StringCase::Lowercase;

        $characters = str_split($input);
        foreach ($characters as $character) {
            if (preg_match('/[a-zA-Z]/', $character)) {
                if ($currentCase == StringCase::Lowercase) {
                    $output .= $this->convertToLowercase($character);
                    $currentCase = StringCase::Uppercase;
                } elseif ($currentCase == StringCase::Uppercase) {
                    $output .= $this->convertToUppercase($character);
                    $currentCase = StringCase::Lowercase;
                }
            } else {
                $output .= $character;
            }
        }

        return $output;
    }

    private function generateCsvFile(string $input, ?string $filename = "file.csv")
    {
        $openedFile = fopen($filename, 'w');

        if ($openedFile === false) {
            $this->error("File cannot be opened: $filename");
            die();
        }

        $characters = str_split($input);
        fputcsv($openedFile, $characters);

        fclose($openedFile);
    }

    private function runAll(): void
    {
        $this->result = $this->convertToUppercase($this->inputString);
        $this->outputToConsole();
        $this->result = $this->convertToAlternateCases($this->inputString);
        $this->outputToConsole();
        $this->generateCsvFile($this->inputString);
        $this->outputToConsole("CSV created!");
    }

    private function outputToConsole(?string $message = null): void
    {
        if ($message) {
            $this->info($message);
        } else {
            $this->info($this->result);
        }
    }

    private function executeChoice(string $optionName)
    {
        $option = array_search($optionName, $this->options);

        if ($option == 0) {
            $this->runAll();
        } elseif ($option == 1) {
            $this->result = $this->convertToUppercase($this->inputString);
            $this->outputToConsole();
        } elseif ($option == 2) {
            $this->result = $this->convertToAlternateCases($this->inputString);
            $this->outputToConsole();
        } elseif ($option == 3) {
            $this->generateCsvFile($this->inputString);
            $this->outputToConsole("CSV created!");
        }
    }
}
