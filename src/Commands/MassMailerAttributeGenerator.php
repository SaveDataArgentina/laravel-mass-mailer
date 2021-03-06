<?php

namespace Simmatrix\MassMailer\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem as File;
use Illuminate\View\Factory as View;

class MassMailerAttributeGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:mass-mailer-attribute {classname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a attribute class for mass mailer';

    /**
     * @var View
     */
    private $view;

    /**
     * @var File
     */
    private $file;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(View $view, File $file)
    {
        parent::__construct();
        $this -> view = $view;
        $this -> file = $file;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {            

            // Get the class name keyed in by user, then create the proper namespace
            $class_name = preg_replace('/[^A-Za-z]/', '', trim($this -> argument('classname')));
            $namespace = config('mass_mailer.app_namespace') . 'Attributes'; 
            $directory = app_path( config('mass_mailer.attribute_path') );
            $new_file_path = sprintf("%s/%s.php", $directory, $class_name);
            $proceed = TRUE;

            if ( file_exists( $new_file_path ) ) {
                $proceed = $this -> confirm('Attribute with the similar name had already been created previously, do you want to overwrite it?');
            }

            if ( $proceed ) {

                $has_value = FALSE;

                if ( $this -> confirm( "Do you want to give a default value for this attribute?" ) ) {

                    $default_value = $this -> ask( "Write down your default value", '' );
                    $is_boolean = in_array( strtolower( $default_value ), ['true', 'false'] );
                    $is_integer = filter_var( $default_value, FILTER_VALIDATE_INT ) !== FALSE;
                    $has_value = TRUE;

                } 

                is_dir( $directory ) ?: $this -> file -> makeDirectory( $directory, 0755, TRUE );

                $view = $this -> view -> make( 'mass_mailer::Generators.attribute', [
                    'namespace'      =>  $namespace,
                    'class_name'     =>  $class_name,
                    'has_value'      =>  $has_value,
                    'default_value'  =>  $default_value,
                    'is_boolean'     =>  $is_boolean,
                    'is_integer'     =>  $is_integer,
                ]);
                $this -> file -> put( $new_file_path, $view -> render() );

                $this -> info( "Your Mass Mailer Attribute {$class_name} has been generated successfully" );
            
            }

        } catch ( \Exception $e ) {
            $this -> error('Failed to create a attribute for your mass mailer ' . $e -> getMessage());
        }
    }
}
