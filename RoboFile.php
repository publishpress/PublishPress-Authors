<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \PublishPressBuilder\PackageBuilderTasks
{
    public function __construct()
    {
        parent::__construct();

        $this->appendToFileToIgnore(
            [
                'webpack.config.js',
                'legacy-tests',
                'src/assets/lib/chosen-v1.8.3/docsupport',
                'src/assets/lib/chosen-v1.8.3/bower.json',
                'src/assets/lib/chosen-v1.8.3/create-example.jquery.html',
                'src/assets/lib/chosen-v1.8.3/create-example.proto.html',
                'src/assets/lib/chosen-v1.8.3/index.proto.html',
                'src/assets/lib/chosen-v1.8.3/options.html',
                'src/assets/lib/chosen-v1.8.3/package.json',
            ]
        );

        $this->setVersionConstantName('PP_AUTHORS_VERSION');
    }
}
