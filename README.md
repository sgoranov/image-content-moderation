# Image Content Moderation

This project provides a machine learning model for image content moderation. It uses 
the Rubix ML library to train a model that can classify images based on predefined 
labels.

**A pre-trained model is included in the repository**, allowing you to test image 
classification without training a new model from scratch. The demo script (_bin/demo.php_) 
uses this pre-trained model to classify images from the _var/demo/_ directory.

## Training the model

You can train the model using the _train.php_ script. The script resizes the 
images and trains a machine learning model using Rubix ML.

#### Parameters

The script accepts a single parameter: the image size for resizing. This will 
set both the width and height of the images to square dimensions.
**If no parameter is provided, the script defaults to 32**.

* 32: Small image size (faster but may have lower accuracy).
* 64: Balanced image size.
* 128: Larger image size (higher accuracy but slower training).

#### Training directory

The script looks for images in the var/train/ directory. Each image should be 
named in the format label_filename.jpg, where label is the class label for 
the image.

For example:
```bash
var/train/safe_001.jpg
var/train/nsfw_002.jpg
```

## Running the demo script
Once a model has been trained (or if you are using the pre-trained model 
included in the repository), you can test image classification using the 
demo script.

####  How to run the demo script
The demo script is located at _bin/demo.php_ and runs predictions on images 
using an already built-in model included in the repository. It processes 
images from the _var/demo/_ directory and prints the classification results.

```bash
composer install
php bin/demo.php
```
#### Expected output
The script will process all images inside the _var/demo/_ directory and output 
the probability that an image is safe.

Example output:

```bash
[2025-03-08T17:01:35.680478+00:00] model-logger.INFO: Starting model testing... [] []
Image: /repo/bin/../var/demo/pexels-abdullahalmallah-8022977.jpg - Safe Probability: 0.80249452699196
[2025-03-08T17:01:35.977932+00:00] model-logger.INFO: Image: /repo/bin/../var/demo/pexels-abdullahalmallah-8022977.jpg - Safe Probability: 0.80249452699196 [] []
Image: /repo/bin/../var/demo/pexels-adria-masi-461420600-28879424.jpg - Safe Probability: 0.8558982541686
[2025-03-08T17:01:36.093578+00:00] model-logger.INFO: Image: /repo/bin/../var/demo/pexels-adria-masi-461420600-28879424.jpg - Safe Probability: 0.8558982541686 [] []
Image: /repo/bin/../var/demo/pexels-damla-selen-demir-429137893-30818059.jpg - Safe Probability: 0.80059629631909
[2025-03-08T17:01:36.299215+00:00] model-logger.INFO: Image: /repo/bin/../var/demo/pexels-damla-selen-demir-429137893-30818059.jpg - Safe Probability: 0.80059629631909 [] []
Image: /repo/bin/../var/demo/pexels-gizem-erol-2149449247-30734874.jpg - Safe Probability: 0.83041478141598
[2025-03-08T17:01:36.444279+00:00] model-logger.INFO: Image: /repo/bin/../var/demo/pexels-gizem-erol-2149449247-30734874.jpg - Safe Probability: 0.83041478141598 [] []
Image: /repo/bin/../var/demo/pexels-onthecrow-30940804.jpg - Safe Probability: 0.89484723244074
[2025-03-08T17:01:36.563376+00:00] model-logger.INFO: Image: /repo/bin/../var/demo/pexels-onthecrow-30940804.jpg - Safe Probability: 0.89484723244074 [] []
Image: /repo/bin/../var/demo/pexels-planeteelevene-30756829.jpg - Safe Probability: 0.96399737379255
[2025-03-08T17:01:36.701463+00:00] model-logger.INFO: Image: /repo/bin/../var/demo/pexels-planeteelevene-30756829.jpg - Safe Probability: 0.96399737379255 [] []
Image: /repo/bin/../var/demo/pexels-reinaldo-30774440.jpg - Safe Probability: 0.87341096050732
[2025-03-08T17:01:37.074268+00:00] model-logger.INFO: Image: /repo/bin/../var/demo/pexels-reinaldo-30774440.jpg - Safe Probability: 0.87341096050732 [] []
[2025-03-08T17:01:37.079228+00:00] model-logger.INFO: Model testing completed. [] []
```

A **probability close to 1.0 means the image is classified as safe**, while a 
probability closer to 0.0 indicates that the image likely contains adult or 
unsafe content.

## Using the pretrained model as a dependency in another project

You can easily integrate the pretrained model from this repository into another 
PHP project using Composer. By including the Git repository in the composer.json 
file of your project, you can install and use the model with Composer.

1. Add the Git repository to composer.json

    In your project, open or create a composer.json file and add the following 
    under the repositories section to include this repository as a dependency:
    
    ```json
    {
        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/sgoranov/image-content-moderation.git"
            }
        ],
        "require": {
            "sgoranov/image-content-moderation": "dev-develop"
        }
    }
    ```

2. Install the dependency

    Once you’ve added the repository to your composer.json, run the following command 
    in your project directory to install the pretrained model as a dependency:
    
    ```bash
    composer install
    ```
    
    This will fetch the repository and all its dependencies, including the 
    pretrained model.

3. Use the pretrained model in your code

After installing the model, you can use it in your project like any other 
dependency. For example, to load the pretrained model and use it for image 
content moderation, you can do the following in your PHP code:

```php
$modelFilePath = '/path/to/vendor/sgoranov/image-content-moderation/var/safe-image-classifier-32.model';

$estimator = PersistentModel::load(new Filesystem($modelFilePath));
$resource = imagecreatefromjpeg($imagePath);
$predictions = $estimator->proba(new Unlabeled([[$resource]]));
```

You can now call the pretrained model and perform image moderation in your project, 
just like in the demo script.

## Docker usage (optional)

If you'd prefer to run the training in a Docker container, you can use the 
provided Dockerfile to build a Docker image with all the dependencies installed.

### Steps to train the model:

1. Build the Docker image:

    The first step is to build the Docker image with the Tensor extension and other 
    dependencies. Run the following command from your project directory:
    
    ```bash
    docker build -t ubuntu22 .
    ```

2. Run the Docker container and train the model:

    Once the image is built, you can use Docker to mount the project directory and execute 
    the training script. This command mounts the local directory to the container and runs 
    the training script located in bin/train.php:
    
    ```bash
    PROJECT_DIR="/home/sgoranov/Projects/image-content-moderation"
    docker run -v $PROJECT_DIR:/repo ubuntu22 bash -c "cd /repo && composer install && php bin/train.php"
    ```
   
3. Run the Docker container and test the model:

    If you want to test the pre-trained model inside a Docker container, you 
    can do so by running the bin/demo.php script within the container.

    ```bash
    PROJECT_DIR="/home/sgoranov/Projects/image-content-moderation"
    docker run -v $PROJECT_DIR:/repo ubuntu22 bash -c "cd /repo && composer install && php bin/demo.php"
    ```

   This will execute the script inside the container, process the images in 
   the var/demo/ directory, and output the classification results.
