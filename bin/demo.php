<?php

namespace Bin;

require_once "vendor/autoload.php";
ini_set('memory_limit', '-1');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Datasets\Unlabeled;

class ModelTester
{
    private Logger $logger;
    private PersistentModel $estimator;
    private string $directory;

    public function __construct(string $modelPath, string $imagesDirectory)
    {
        // Initialize logger
        $this->logger = new Logger('model-logger');
        $this->logger->pushHandler(new StreamHandler('php://stdout'));

        // Load the pre-trained model
        $this->estimator = PersistentModel::load(new Filesystem($modelPath));
        $this->directory = $imagesDirectory;

        if (!is_dir($this->directory)) {
            $this->logger->error("Image directory does not exist: " . $this->directory);
            exit(1);
        }
    }

    public function testModel()
    {
        $this->logger->info("Starting model testing...");

        // Loop through images in the directory and classify them
        foreach (glob($this->directory . '/*.jpg') as $path) {
            $this->processImage($path);
        }

        $this->logger->info("Model testing completed.");
    }

    private function processImage(string $imagePath): void
    {
        if (!file_exists($imagePath)) {
            $this->logger->warning("Image does not exist: $imagePath");
            return;
        }

        // Load image resource
        $resource = imagecreatefromjpeg($imagePath);
        if (!$resource) {
            $this->logger->warning("Failed to load image: $imagePath");
            return;
        }

        // Create dataset for the model prediction
        $dataset = new Unlabeled([[$resource]]);
        $proba = $this->estimator->proba($dataset);

        // Get safe probability from the model prediction
        $safeProbability = $proba[0]['safe'] ?? 'N/A';

        echo "Image: $imagePath - Safe Probability: $safeProbability" . PHP_EOL;

        // Log the result
        $this->logger->info("Image: $imagePath - Safe Probability: $safeProbability");
    }
}

// Usage
$modelPath = __DIR__ . '/../var/safe-image-classifier-32.model';
$imagesDirectory = __DIR__ . '/../var/demo';

$modelTester = new ModelTester($modelPath, $imagesDirectory);
$modelTester->testModel();
