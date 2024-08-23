<?php

namespace App\Http\Controllers\Yogagraphy;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use App\Mail\Upljft\Yogagraphy\YogagraphyMailSender;
use App\Models\ImageModel;
use App\Models\Yogagraphy\CustomerExcelImport;
use App\Models\Yogagraphy\YogagraphyTypes;
use App\Models\Upljft\Yogagraphy\YogaForm;
use App\Models\Upljft\Yogagraphy\YogagraphyMail;
use App\Models\Yogagraphy\YogagraphyOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Intervention\Image\Facades\Image;
use \DrewM\MailChimp\MailChimp;
use function GuzzleHttp\Psr7\str;
use Inertia\Inertia;
use Inertia\Response;

class YogagraphyController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function viewForm(Request $request): Response
    {
        return Inertia::render('Yogagraphy/Form');
    }
    /**
     * The the YogagraphyController
     *
     * @param string $name
     * @return string
     */
    public function displayFinalChristmasCardByName(string $name)
    {
        return $this->getYogaImageForMail($name, new YogagraphyOptions())->response();
    }

    /**
     * The the YogagraphyController
     *
     * @param string $name
     * @return string
     */
    public function displayYogasByName(string $name)
    {
        //uppercase the name cause all images filenames are in uppercase
        $name = strtoupper($name);
        //put every character out of the name string into a array
        $name_array = str_split($name);

        return $this->renderYogas($name_array, new YogagraphyOptions())->response();
    }

    //Yoga.blade.php form get image
    public function getImageByForm(Request $request){
        $name = $request->input('name');
        $type = $request->input('type');
        $image_resolution_height = $request->input('resolutionHeight');

        $response = new \stdClass();
        $response->{'image'} = null;
        $response->{'error'} = '';

        $final_img = new Image();
        switch ($type) {
            case 'img':
                //uppercase the name cause all images filenames are in uppercase
                $name = strtoupper($name);

                //put every character out of the name string into a array
                $name_array = str_split($name);

                error_log(json_encode($name_array));
                $yoga_options = new YogagraphyOptions();
                $yoga_options->setYogaImgHeight($image_resolution_height);
                $yoga_options->setYogaImgWidth($image_resolution_height);
                $yoga_options->setBackgroundHeight($image_resolution_height);

                $response->image = $this->renderYogas($name_array, $yoga_options)->encode('data-url');
                break;
            case 'card':
                $response->image = $this->getChristmasCardByName($name, new YogagraphyOptions())->encode('data-url');
                break;
            default:
                $response->error = 'No valid image type. Type ['.$type.']';
                break;
        }
        return response()->json($response);
    }

    /**
     * Get a Image with the filled images
     *
     * @param  string  $name, YogagraphyOptions $yogagraphyOptions (optional)
     * @return \Intervention\Image\Image
     */
    private function getChristmasCardByName(string $name, YogagraphyOptions $yogagraphyOptions){
        $yogagraphyOptions->setType(YogagraphyTypes::$christmas_card);

        $header_image = new ImageModel();
        $header_image->setSrc('assets/img/yogagraphy/mail_header.png');

        // open the header image
        $header_img = Image::make($header_image->getSrc());

        $final_yoga_image = new ImageModel();
        $final_yoga_image->setSrc('assets/img/yogagraphy/yogagraphy_background.png');


        // open the yoga image background
        $final_yoga_img = Image::make($final_yoga_image->getSrc());
        $yogagraphyOptions->setBackgroundWidth($final_yoga_img->width());
        $yogagraphyOptions->setBackgroundHeight($final_yoga_img->height());

        //uppercase the name cause all images filenames are in uppercase
        $name = strtoupper($name);

        //put every character out of the name string into a array
        $name_array = str_split($name);

        //Get all yoga images rendered on a transparent background
        $yoga_images = $this->renderYogas( $name_array,$yogagraphyOptions);
        //Insert the yoga to the final background
        $final_yoga_img->insert($yoga_images, 'center');

        $footer_image = new ImageModel();
        $footer_image->setSrc('assets/img/yogagraphy/mail_footer.png');

        // open the footer image
        $footer_img = Image::make($footer_image->getSrc());

        $final_image = new ImageModel();
        $final_image->setSrc('assets/img/transparent_background.png');

        // open an image file
        $final_img = Image::make($final_image->getSrc());
        $final_img->resize($header_img->width(), ($header_img->height() + $final_yoga_img->height() + $footer_img->height()));

        $final_img->insert($header_img, 'top-left', 0, 0);
        $final_img->insert($final_yoga_img, 'top-left', 0, $header_img->height());
        $final_img->insert($footer_img, 'top-left', 0, ($header_img->height() + $final_yoga_img->height()) );


        return $final_img;
    }

    private function renderYogas(array $name_array, YogagraphyOptions $yogagraphyOptions){
        $background_image = new ImageModel();

        $background_image->setHeight($yogagraphyOptions->getBackgroundHeight());
        $background_image->setSrc('assets/img/transparent_background.png');

        // open an image file
        $final_img = Image::make($background_image->getSrc());

        $yoga_img_x = 0;
        $yoga_img_y = 0;

        $yoga_image = new ImageModel();
        $yoga_image->setWidth($yogagraphyOptions->getYogaImgWidth());
        $yoga_image->setHeight($yogagraphyOptions->getYogaImgHeight());

        $sum_img_yoga_width = 0;
        $sum_img_yoga_height = 0;
        foreach ($name_array as $key=>$char) {
            //If its a space character display a transparent background with the yoga sizes
            //Also we check if the image with this character exist
            if($char === " " || file_exists("assets/img/yogagraphy/".$char.".png")){
                //We need full width of all inserted images
                $sum_img_yoga_width += $yoga_image->getWidth();
            }
        }

        $max_yoga_width = $sum_img_yoga_width;
        if($yogagraphyOptions->getType() === YogagraphyTypes::$christmas_card){
            if($sum_img_yoga_width > ($yogagraphyOptions->getBackgroundWidth() - 80)){
                //If the sum of yogas is to wide we need to make the yogas smaller
                $calc_yoga_size = $this->calcYogaSize($yogagraphyOptions, count($name_array));
                error_log("calc yoga size: ".$calc_yoga_size);
                $yoga_image->setWidth($calc_yoga_size);
                $yoga_image->setHeight($calc_yoga_size);

                //The max width for yogas
                $max_yoga_width = $calc_yoga_size * count($name_array);
            }
        }else{
            $background_image->setWidth($yogagraphyOptions->getBackgroundWidth());
        }
        // now you are able to resize the instance
        $final_img->resize($max_yoga_width, $background_image->getHeight());

        foreach ($name_array as $key=>$char) {
            //If its a space character display a transparent background with the yoga sizes
            if($char === " " || file_exists("assets/img/yogagraphy/".$char.".png")){
                if($char === " "){
                    $yoga_image->setSrc("assets/img/transparent_background.png");
                }else if(file_exists("assets/img/yogagraphy/".$char.".png")) {
                    $yoga_image->setSrc("assets/img/yogagraphy/" . $char . ".png");
                }
                $yoga_img = Image::make($yoga_image->getSrc());
                // prevent possible upsizing
                $yoga_img->resize($yoga_image->getWidth(), $yoga_image->getHeight());

                if($key !== 0){
                    //Dont do this at the first item otherwise we get the x value equal the image width but we dont need at first item
                    $yoga_img_x = $yoga_img_x + ($yoga_image->getWidth());
                }


                $final_img->insert($yoga_img, "left",$yoga_img_x, $yoga_img_y);

            }


        }


        return $final_img;
    }
    private function calcYogaSize(YogagraphyOptions $yogagraphyOptions, $char_count){
        $new_yoga_size = 50;
        for($i = 1; $i < 5; $i++){
            //We count until 5 cause we max downsize to 10px
            $new_sum_yoga_width = ($yogagraphyOptions->getYogaImgWidth() - ($i * 10)) * $char_count;
            // - 80 cause there are borders in the background image
            if($new_sum_yoga_width <= ($yogagraphyOptions->getBackgroundWidth() - 80)){
                $new_yoga_size = $yogagraphyOptions->getYogaImgWidth() - ($i * 10);
                break;
            }
        }
        return $new_yoga_size;
    }

    private function getYogaImageForMail(string $name, YogagraphyOptions $yogagraphyOptions){
        $yogagraphyOptions->setType(YogagraphyTypes::$christmas_card);

        $final_yoga_image = new ImageModel();
        $final_yoga_image->setSrc('assets/img/yogagraphy/layout/yogagraphy_background.jpg');
        // open the yoga image background
        $final_yoga_img = Image::make($final_yoga_image->getSrc());
        $yogagraphyOptions->setBackgroundWidth($final_yoga_img->width());
        $yogagraphyOptions->setBackgroundHeight($final_yoga_img->height());

        //uppercase the name cause all images filenames are in uppercase
        $name = strtoupper($name);

        //put every character out of the name string into a array
        $name_array = str_split($name);

        //Get all yoga images rendered on a transparent background
        $yoga_images = $this->renderYogas( $name_array,$yogagraphyOptions);

        //Insert the yoga to the final background
        $final_yoga_img->insert($yoga_images, 'center');

        return $final_yoga_img;
    }
    public function getLongestNames(Request $request){
        $customers = (new CustomerExcelImport)->toArray($request->file('data'));

        $customers_filtered = array_filter($customers[0], function($value) { return $value['mail_address'] !== null ;});

        //$last_names = array();
        //$first_names = array();

        $longest_first_name_customer = array("count" => 0, "customer" => null);
        $longest_last_name_customer = array("count" => 0, "customer" => null);;
        foreach ($customers_filtered as $customer){
            //Get Longest Name and Lastname
            if ($customer['anrede'] === 'Du') {
                //array_push($first_names, $customer['name']);
                if(strlen($customer['name']) > $longest_first_name_customer["count"]){
                    $longest_first_name_customer["count"] = strlen($customer['name']);
                    $longest_first_name_customer["customer"] = $customer;
                }
            } else if ($customer['anrede'] === 'Sie') {
                //array_push($last_names, $customer['name_last']);
                if(strlen($customer['name_last']) > $longest_last_name_customer["count"]){
                    $longest_last_name_customer["count"] = strlen($customer['name_last']);
                    $longest_last_name_customer["customer"] = $customer;
                }
            }
        }

        return view('/upljft/yogagraphy/upload', ['longest_last_name_customer' => $longest_last_name_customer, 'longest_first_name_customer' => $longest_first_name_customer]);
    }
    /**
     * Render images and return it to the upload view
     *
     * @param Request $request
     * @return View
     */
     public function uploadSheet(Request $request){
         ini_set('max_execution_time', 500);

         $customers = (new CustomerExcelImport)->toArray($request->file('data'));

         $customers_filtered = array_filter($customers[0], function($value) { return $value['mail_address'] !== null ;});

         $mails_sent = 0;
         //The array out of excel is a multidemensional array. Why?
         //TODO can we use one dimensional array here ? Now we have to go to index [0] to enter
         $imageUploads = array();
         if(count($customers_filtered) > 0){
             $mailchimp_files = $this->getMailchimpFiles();

             foreach ($customers_filtered as $customer){

                     $customer_name = '';
                     if($customer['anrede'] === 'Du'){
                         $customer_name = $customer['name'];
                     }else if($customer['anrede'] === 'Sie'){
                         $customer_name = $customer['name_last'];
                     }else if($customer_name !== null){
                         $customer_name = $customer['name'];
                     }
                     $customer_img = $this->getYogaImageForMail($customer_name, new YogagraphyOptions());

                     $imageUpload = $this->uploadImageToMailchimp($customer_img, $customer, $mailchimp_files);

                     $this->uploadCustomerToMailchimp($customer, $imageUpload);

                     array_push($imageUploads,$imageUpload);

                     //Needed so we can access the base 64 string of the image
                     base64_encode($customer_img->encode()->encoded);

                     /*$yogagraphyMail = new YogagraphyMail();
                     $yogagraphyMail->mail = $customer['email'];
                     $yogagraphyMail->name = $customer['name'];
                     $yogagraphyMail->subject = "test subject";
                     $yogagraphyMail->title = "Moin";
                     $yogagraphyMail->customerImg = $customer_img;

                     Mail::to($yogagraphyMail->mail)->send(new YogagraphyMailSender($yogagraphyMail));

                     $mails_sent++;*/

             }
         }
         //return response()->json($final_images);
         return view('/upljft/yogagraphy/upload', ['mails_sent' => $mails_sent, 'imageUploads' => $imageUploads]);
     }

    private function uploadImageToMailchimp(\Intervention\Image\Image $image, $customer, $mailchimp_files){
         $existing_file = $this->mailchimpFileExist($customer['name'].".png", $mailchimp_files);
         if(!$existing_file){
             error_log("image will be added");
             try {
                 $mailchimp = new MailChimp(getenv('MC_KEY'));
                 $response = $mailchimp->post("/file-manager/files",
                     //Folder ID 4683 = yogagraphy on mailchimp
                     array("folder_id" => 4683, "name" => $customer['name'].".png",
                         "file_data" => base64_encode($image->encode()->encoded))
                 );

                 return $response;
             } catch (\Exception $e) {
                 error_log($e);
             }
             return null;
         }else{
             error_log("image already exist");
             return $existing_file;
         }
    }

    private function mailchimpFileExist($filename, $mailchimp_files){

        if($mailchimp_files !== null){
            foreach ($mailchimp_files["files"] as $item){
                if (isset($item["name"]) && $item["name"] === $filename){
                    return $item;
                }
            }

            return false;
        }
        return null;
    }
    private function getMailchimpFiles(){
        try {
            $mailchimp = new MailChimp(getenv('MC_KEY'));
            $response = $mailchimp->get("/file-manager/files",
                //Folder ID 4683 = yogagraphy on mailchimp
                array("fields" => array("files"), "created_by" => "Upljft Developer", "count" => 1000)
            );
            return $response;
        } catch (\Exception $e) {
            error_log($e);
        }
        return null;
    }


    private function uploadCustomerToMailchimp($customer, $imageUploadResponse){
            try {

                $mailchimp = new MailChimp(getenv('MC_KEY'));
                //List ID 58d788dd73  = Christman 2019 Audience in mailchimp - json_encode($mailchimp->getLists(['email' => 'niklas.grieger@upljft.com', 'fields' => 'lists.id']));
                //OR https://us17.admin.mailchimp.com/lists/settings/defaults?id=192207
                $list_id = '58d788dd73';
                $customer_mail = $customer['mail_address'];
                $customer_gender = $customer['gender'];
                $customer_name = '';
                switch ($customer['anrede']){
                    case 'Sie':
                        $customer_name = $customer['name_last'];
                        break;
                    default:
                        $customer_name = $customer['name'];
                        break;
                }

                $md5_adress = md5($customer_mail);

                $put_response = $mailchimp->put("lists/$list_id/members/".$md5_adress, [
                    'email_address' => $customer_mail,
                    'status_if_new' => 'subscribed',
                    'merge_fields' => ['NAME' => $customer_name, 'GENDER' => $customer_gender, 'YOGAGRAPHY' => $imageUploadResponse['full_size_url']]
                ]);

                /*$post_response = $mailchimp->post("lists/$list_id/members", [
                    'email_address' => $customer_mail,
                    'status' => 'subscribed',
                    'merge_fields' => ['NAME' => $customer_name, 'GENDER' => $customer_gender, 'YOGAGRAPHY' => $imageUploadResponse['full_size_url']]
                ]);*/

                error_log("Mailchimp contact added: ".json_encode($put_response));

            } catch (\Exception $e) {
                error_log($e);
            }
    }
}
