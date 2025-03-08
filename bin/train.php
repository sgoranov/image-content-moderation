<?php

namespace Bin;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\ImageResizer;
use Rubix\ML\Transformers\ImageVectorizer;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\Layers\Dropout;
use Rubix\ML\NeuralNet\Layers\BatchNorm;
use Rubix\ML\NeuralNet\ActivationFunctions\ELU;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Persisters\Filesystem;


define('ROOT_DIR', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..'));
define('TRAIN_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'train');
define('MODEL_FILE', ROOT_DIR . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'new.model');

// Default image size is 32 if not provided
// single parameter for size (e.g., 32, 64, or 128)
$size = $argv[1] ?? 32;

require_once ROOT_DIR . "/vendor/autoload.php";
ini_set('memory_limit', '-1');

$logger = new Logger('rubixml');
$logger->pushHandler(new StreamHandler('php://stdout'));

if (!is_dir(TRAIN_DIR) || !is_readable(TRAIN_DIR)) {
    $logger->error("Training directory does not exist or is not readable: " . TRAIN_DIR);
    exit(1);
}

$logger->info("Loading and processing training images...");

$samples = [];
$labels = [];
$files = glob(TRAIN_DIR . '/*.jpg');

if (!$files) {
    $logger->error("No images found in training directory.");
    exit(1);
}

foreach ($files as $file) {
    $image = imagecreatefromjpeg($file);

    if (!$image) {
        $logger->warning("Skipping invalid image: $file");
        continue;
    }

    // Extract label from filename (assuming format "label_filename.jpg")
    $filename = basename($file);
    $labelParts = explode('_', $filename);
    $label = $labelParts[0] ?? 'unknown';

    $samples[] = [$image];
    $labels[] = $label;
}

if (empty($samples)) {
    $logger->error("No valid images were loaded.");
    exit(1);
}

$dataset = new Labeled($samples, $labels);

// Define model pipeline
$estimator = new PersistentModel(
    new Pipeline([
        new ImageResizer($size, $size),
        new ImageVectorizer(),
        new ZScaleStandardizer(),
    ], new MultilayerPerceptron([
        new Dense(200),
        new Activation(new ELU()),
        new Dropout(0.5),
        new Dense(200),
        new Activation(new ELU()),
        new Dropout(0.5),
        new Dense(100, 0.0, false),
        new BatchNorm(),
        new Activation(new ELU()),
        new Dense(100),
        new Activation(new ELU()),
        new Dense(50),
        new Activation(new ELU()),
    ], 256, new Adam(0.0005))),
    new Filesystem(MODEL_FILE, true)
);

$logger->info("Training model...");

$estimator->train($dataset);
$estimator->save();

$logger->info("Model successfully saved to " . MODEL_FILE);
