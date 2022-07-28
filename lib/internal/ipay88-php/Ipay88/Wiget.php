<?php
/**
 * Ipay88 Inc
 * @author Ipay88 Inc <pvchi@ipay88.com>
 * @package Ipay88\Lib
 */

class Ipay88_Wiget
{
    protected $form;

    protected $action;

    protected $data;

    protected $merchantCode;

    protected $merchantKey;

    protected $paymentId;

    protected $title;

    protected $imagePath;

    protected $formName;

    /**
     * @return mixed
     */
    public function getFormName()
    {
        return $this->formName;
    }

    /**
     * @param mixed $formName
     */
    public function setFormName($formName)
    {
        $this->formName = $formName;
    }




    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param mixed $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }



    public function __construct($data = array(), $action=null)
    {
        if(is_array($data) && !empty($data)) {
            $this->data = $data;
        }
        if($action) {
            $this->action = $action;
        }
    }

    public function generateRedirectForm() {
        return $this->renderHtmlRediectForm();
    }

    public function renderHtmlRediectForm() {
        $action = $this->getAction() ? : '';
        $formName = $this->getFormName() ? : 'ipay88PaymentRedirectForm';
        $htmlCode =
            '<form style="text-align:center;" name="'.$formName.'"  method="POST" action="'.$action.'">';
                $options = $this->getData();

                if(is_array($options) && count($options)) {
                    foreach ($options as $key => $option) {
                        $htmlCode .= '<input type="hidden" name="'.$key.'" value="'.$option.'" />';
                    }
                }
                // image
                $loadingImage = $this->getImagePath() ? : '';
                $htmlCode .=
                    '<div align="center" style="width:100%">
                        <p>
                            '.$this->getTitle().'
                        </p>
                        <img src="'.$loadingImage.'" border="0">
                    </div>';

                $htmlCode .=
                    '<input type="submit" class="hide" value="Pay Now" />
            </form>';

            // Auto submit
        $actionFormName = "document.ipay88PaymentRedirectForm.submit()";
        if($formName) {
            $actionFormName = "document.".$formName.".submit()";
        }
        $htmlCode .=
            '<script type="text/javascript">
                setTimeout('.$actionFormName.', 3000);
            </script>';
        return $htmlCode;
    }
}