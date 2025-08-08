<?php

namespace Classes;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class File
{
    public static function generatePDF(array $dataPDF)
    {

        $info = $dataPDF[0];
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $bootstrapCSS = file_get_contents('vendor/twbs/bootstrap/dist/css/bootstrap.min.css');
        function formatValue($value)

        {

            $pathTrue = 'assets/images/true.png';
            $pathFalse = 'assets/images/false.png';
            $typeTrue = pathinfo($pathTrue, PATHINFO_EXTENSION);
            $typeFalse = pathinfo($pathFalse, PATHINFO_EXTENSION);
            $dataTrue = file_get_contents($pathTrue);
            $dataFalse = file_get_contents($pathFalse);
            $base64True = 'data:image/' . $typeTrue . ';base64,' . base64_encode($dataTrue);
            $base64False = 'data:image/' . $typeFalse . ';base64,' . base64_encode($dataFalse);
            return ($value === "1") ? "<img src='" . $base64True . "' width='10' height='10'>" : ($value === "0" ? "<img src='" . $base64False . "' width='10' height='10'>" : $value);
        }



        $path = 'assets/images/logo_ceraor.png';
        $pathCruze = 'assets/images/cruceta.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $typeCruze = pathinfo($pathCruze, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $dataCruze = file_get_contents($pathCruze);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $base64Cruze = 'data:image/' . $typeCruze . ';base64,' . base64_encode($dataCruze);

        if (array_key_exists('barcode', $info)) {
            $pathBarcode = "appointments-barcodes/" . $info['barcode'];
            $typeBarcode = pathinfo($pathBarcode, PATHINFO_EXTENSION);
            $dataBarcode = file_get_contents($pathBarcode);
            $base64Barcode = 'data:image/' . $typeBarcode . ';base64,' . base64_encode($dataBarcode);
        } else {

            $pathBarcode = $info['barcode'] = "assets/images/sin-folio.png";

            $typeBarcode = pathinfo($pathBarcode, PATHINFO_EXTENSION);

            $dataBarcode = file_get_contents($pathBarcode);

            $base64Barcode = 'data:image/' . $typeBarcode . ';base64,' . base64_encode($dataBarcode);
        }



        if (!array_key_exists('code', $info)) {

            $info['code'] = "sin-folio";
        }

        if (!array_key_exists('order_created_at', $info)) {

            $info['order_created_at'] =  date("Y-m-d");

            echo $info['order_created_at'];

            /*  die(); */
        }



        /* echo getcwd();

        die(); */

        // Estructura HTML con secciones bien organizadas

        $html = "

        <!DOCTYPE html>

        <html lang='es'>

        <head>

            <meta charset='UTF-8'>

            <meta name='viewport' content='width=device-width, initial-scale=1.0'>

            <title>Reporte PDF</title>

           <style rel='stylesheet'>" . $bootstrapCSS . "</style>



        </head>

        <body>

        <table style='width: 100% !important; table-layout: fixed !important;'>

                <thead>

                    <th style='font-size: 11px'>Villahermosa</th>

                    <th style='font-size: 11px'>Cárdenas</th>

                    <th style='font-size: 11px'>Comalcalco</th>

                    <th style='font-size: 11px'>Tuxtla Gutiérrez</th>

                </thead>

                <tr>

                    <td style='background-color: #f2f2f2 !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr>

                                <td style='font-size: 10px'><b>Dirección:</b> Blvd. Adolfo Ruiz Cortines  No.804 Magisterial, Vhsa., Tab. C.P. 86040</td>

                            </tr>

                            <tr>

                                <td style='font-size: 10px'><b>Teléfono(s):</b> 993-324-6453, 993-314-4353, 993-151-9846, 993-151-9847</td>

                            </tr>

                            <tr>

                                <td style='font-size: 10px'><b>WhatsApp:</b> 993-264-3105</td>

                            </tr>

                        </table>

                    </td>

                    <td style='background-color: #f2f2f2 !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr>

                                <td style='font-size: 10px'><b>Dirección:</b> Av. Lázaro Cárdenas No. 1000 Local 20 Plaza Aqua, Col. Centro, Cárdenas, Tabasco. C.P.86500</td>

                            </tr>

                            <tr>

                                <td style='font-size: 10px'><b>Teléfono(s):</b> 937-668-5556, 937-668-5624</td>

                            </tr>

                            <tr>

                                <td style='font-size: 10px'><b>WhatsApp:</b> 937-108-2076</td>

                            </tr>

                        </table>

                    </td>

                    <td style='background-color: #f2f2f2 !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr>

                                <td style='font-size: 10px'><b>Dirección: </b>Calle Bicentenario Manzana 1 Lote 8, Fracc. Santo Domingo, (frente al ADO) Comalcalco, Tab. C.P. 86340</td>

                            </tr>

                            <tr>

                                <td style='font-size: 10px'><b>Teléfono(s): </b> 933-109-4400</td>

                            </tr>

                            <tr>

                                <td style='font-size: 10px'><b>WhatsApp: </b>933-129-6910</td>

                            </tr>

                        </table>

                    </td>

                    <td style='background-color: #f2f2f2 !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr>

                                <td style='font-size: 10px'><b>Dirección: </b>Calle San Francisco El Sabinal 228 Planta Baja, Col. San Francisco Sabinal, Tuxtla Gutiérrez, Chiapas. C.P. 29020</td>

                            </tr>

                            <tr>

                                <td style='font-size: 10px'><b>Teléfono(s): </b>961-125-9687</td>

                            </tr>

                            <tr>

                                <td style='font-size: 10px'><b>WhatsApp: </b> 961-367-9746</td>

                            </tr>

                        </table>

                    </td>

                </tr>

            </table>

        <table style='width: 100%;'>

            <tr>

                <td style='width: 50%; text-align: left;'>

                    <img src='" . $base64 . "' width='100' height='100'>

                </td>

                <td style='width: 50%; text-align: left;'>

                    <center>

                        <img src='" . $base64Barcode . "' width='200' height='50'>

                    </center>

                </td>

                <td style='width: 50%; text-align: right; vertical-align: middle;'>

                    <label style='display: block; text-align: right;'>Fecha: " . $info['order_created_at'] . "</label>

                </td>

            </tr>

        </table>     

        <center>

            <h3 style= padding: none !important; margin: none !important>ORDEN DE ESTUDIOS</h3>

        </center>

            <table style='width: 100% !important; table-layout: fixed !important; border-spacing: 10px !important;'>

                <thead>

                    <th style='font-size: 12px'>Folio: " . $info['code'] . "</th>

                </thead>

                <tr>

                    <td style='width: 100% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table style='width: 100%; border-collapse: collapse; text-align: center;'>

                            <tr>

                                <th style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>Paciente</th>

                                <th style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>F. Nacimiento</th>

                                <th style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>Dirección</th>

                                <th style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>E-mail</th>

                                <th style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>Teléfono</th>

                            </tr>

                            <tr>

                                <td style='font-size: 11px; border: 1px solid #ddd; padding: 3px;'>" . formatValue($info['patient']) . "</td>

                                <td style='font-size: 11px; border: 1px solid #ddd; padding: 3px;'>" . formatValue($info['birthdate']) . "</td>

                                <td style='font-size: 11px; border: 1px solid #ddd; padding: 3px;'>" . formatValue($info['address']) . "</td>

                                <td style='font-size: 11px; border: 1px solid #ddd; padding: 3px;'>" . formatValue($info['email']) . "</td>

                                <td style='font-size: 11px; border: 1px solid #ddd; padding: 3px;'>" . formatValue($info['phone']) . "</td>

                            </tr>

                        </table>

                    </td>



                </tr>

                <tr>

                <td>

                    <table style='width: 100%; border-collapse: collapse; text-align: center;  background-color: #e0f7fa !important;'>

                        <tr>

                            <th style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>Doctor</th>

                            <th style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>Cédula</th>

                        </tr>

                        <tr>

                            <td style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>" . formatValue($info['doctor']) . "</td>

                            <td style='font-size: 12px; border: 1px solid #ddd; padding: 3px;'>" . formatValue($info['professional_id']) . "</td>

                        </tr>

                    </table>

                </td>



                </tr>

            </table>

            <table style='width: 100% !important; table-layout: fixed !important; border-spacing: 10px !important;'>

                <thead>

                    <th style='font-size: 12px'>

                        Radiografías

                    </th>

                    <th>

                    </th>

                    <th style='font-size: 12px'>

                        Análisis Cefalométricos

                    </th>  

                </thead>

                <tr>

                    <td style='width: 33.3% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>Rx Panorámica </td><td>" . formatValue($info['rx_panoramic']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Rx Arcada Panorámica </td><td>" . formatValue($info['rx_arc_panoramic']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Rx Lateral de Cráneo </td><td>" . formatValue($info['rx_lateral_skull']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Rx AP Cráneo </td><td>" . formatValue($info['ap_skull']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Rx PA Cráneo </td><td>" . formatValue($info['pa_skull']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Senos Paranasales </td><td>" . formatValue($info['paranasal_sinuses']) . "</td></tr>

                        </table>

                    </td>

                     <td style='width: 33.3% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>ATM Apertura y Cierre </td><td>" . formatValue($info['atm_open_close']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Perfilograma </td><td>" . formatValue($info['profilogram']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Cráneo de Watters </td><td>" . formatValue($info['watters_skull']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Palmar y Digitales </td><td>" . formatValue($info['palmar_digit']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Otros </td><td>" . formatValue($info['others_radiography']) . "</td></tr>

                        </table>

                    </td>

                    <td style='width: 33.3% !important; background-color: #e0f7fa !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>Rickets</td><td>" . formatValue($info['rickets']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>McNamara</td><td>" . formatValue($info['mcnamara']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Downs</td><td>" . formatValue($info['downs']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Jaraback</td><td>" . formatValue($info['jaraback']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Steiner</td><td>" . formatValue($info['steiner']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Otros</td><td>" . formatValue($info['others_analysis']) . "</td></tr>

                        </table>

                    </td>

                </tr>

            </table>

            <table style='width: 100% !important; table-layout: fixed !important; border-spacing: 10px !important;'>

                <thead>

                    <th style='font-size: 12px'>

                        Radiografías Intraorales

                    </th>

                    <th></th>

                    <th style='font-size: 12px'>

                        Modelos de Estudio  

                    </th>

                    <th style='font-size: 12px'>

                        Estereolitografía (MAXILAR)

                    </th>

                </thead>

                <tr>

                    <td style='width: 25% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>Oclusal</td><td>" . formatValue($info['occlusal_xray']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Superior</td><td>" . formatValue($info['superior']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Inferior</td><td>" . formatValue($info['inferior']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Serie Periapical Completa</td><td>" . formatValue($info['complete_periapical']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Individual Periapical</td><td>" . formatValue($info['individual_periapical']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Conductometría</td><td>" . formatValue($info['conductometry']) . "</td></tr>

                        </table>

                    </td>

                    <td style='width: 30% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <img src='" . $base64Cruze . "' width='200' height='80'>

                    </td>

                    <td style='width: 20% !important; background-color: #e0f7fa !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>Risina</td><td>" . formatValue($info['risina']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>DentalPrint</td><td>" . formatValue($info['dentalprint']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Impresión 3D Resina</td><td>" . formatValue($info['3d_risina']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Guía Quirúrgica</td><td>" . formatValue($info['surgical_guide']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Pieza de Estudio</td><td>" . formatValue($info['studio_piece']) . "</td></tr>

                        </table>

                    </td>

                    <td style='width: 20% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>Superior</td><td>" . formatValue($info['maxilar_superior']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Inferior</td><td>" . formatValue($info['maxilar_inferior']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Ambos</td><td>" . formatValue($info['maxilar_both']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Otros</td><td>" . formatValue($info['maxilar_others']) . "</td></tr>

                        </table>

                    </td>

                    

                </tr>

            </table>

            <table style='width: 100% !important; table-layout: fixed !important; border-spacing: 10px !important;'>

                <thead>

                    <th style='font-size: 12px'>Tomografía 3D</th>

                </thead>

                <tr>

                    <td style='width: 33.33% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>Tomografía Completa</td><td>" . formatValue($info['complete_tomography']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Tomografía Ambos Maxilares</td><td>" . formatValue($info['two_jaws_tomography']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Tomografía Maxilar</td><td>" . formatValue($info['maxilar_tomography']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Tomografía Mandíbula</td><td>" . formatValue($info['jaw_tomography']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Tomografía SNP</td><td>" . formatValue($info['snp_tomography']) . "</td></tr>

                        </table>

                    </td>

                    <td style='width: 33.33% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>Tomografría Oído</td><td>" . formatValue($info['ear_tomography']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Tomografía ATM Boca Abierta/Cerrada</td><td>" . formatValue($info['atm_tomography_open_close']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Tomografía ATM Boca Abierta</td><td>" . formatValue($info['lateral_left_tomography_open_close']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Tomografía ATM Boca Cerrada</td><td>" . formatValue($info['lateral_right_tomography_open_close']) . "</td></tr>

                        </table>

                    </td>

                    <td style='width: 33.33% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>ONDEMAND: </td><td>" . formatValue($info['ondemand']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>DICOM: </td><td>" . formatValue($info['dicom']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Pieza #: </td><td>" . formatValue($info['tomography_piece']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Diente Retenido: </td><td>" . formatValue($info['impacted_tooth']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Otros: </td><td>" . formatValue($info['others_tomography']) . "</td></tr>

                        </table>

                    </td>

                </tr>   

            </table>

            <table style='width: 100% !important; table-layout: fixed !important; border-spacing: 10px !important;'>

                <thead>

                    <th style='font-size: 12px'>Fotografía Clínica Intraoral y Extraoral</th>

                    <th style='font-size: 12px'>Tipo de Formato</th>

                    <th style='font-size: 12px'>Análisis de Modelo</th>

                    <th style='font-size: 12px'>Escaneo Intraoral</th>

                </thead>

                <tr>

                    <td style='width: 25% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>F.C.I. y Ext.</td><td>" . formatValue($info['clinical_photography']) . "</td></tr>

                        </table>

                    </td>

                    <td style='width: 25% !important; background-color: #e0f7fa !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>

                        <table>

                            <tr style='font-size: 11px !important;'><td>Acetato</td><td>" . formatValue($info['acetate_print']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Papel Backlight</td><td>" . formatValue($info['paper_print']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>E-mail</td><td>" . formatValue($info['send_email']) . "</td></tr>

                        </table>

                    </td>

                    <td style='width: 25% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>                

                        <table>

                            <tr style='font-size: 11px !important;'><td>Bolton</td><td>" . formatValue($info['analysis_bolton']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Moyers</td><td>" . formatValue($info['analysis_moyers']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>Otros</td><td>" . formatValue($info['others_models_analysis']) . "</td></tr>

                        </table>

                    </td>

                     <td style='width: 25% !important; background-color: #f2f2f2 !important; padding: 10px !important; border-radius: 1px !important; vertical-align: top !important;'>                

                        <table>

                            <tr style='font-size: 11px !important;'><td>STL</td><td>" . formatValue($info['stl']) . "</td><td style='font-size: 11px !important;'>Invisaligh</td><td>" . formatValue($info['invisaligh']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>OBJ</td><td>" . formatValue($info['obj']) . "</td><td style='font-size: 11px !important;'>Otros</td><td>" . formatValue($info['others_scanners']) . "</td></tr>

                            <tr style='font-size: 11px !important;'><td>PLY</td><td>" . formatValue($info['ply']) . "</td></tr>

                        </table>

                    </td>

                </tr>

            </table>

            <label style='font-size: 11px !important;'>Interpretación Odontológica: </label>" . formatValue($info['dental_interpretation']) . " 

            </div>

            <br>

            <label style='font-size: 11px !important;'>Otros:_________________________________________</label>

            <br>

            <center>

            <div>

            ____________________

            <br><label style='font-size: 11px !important;'>Firma</label>

            <div>

            

            <center>

        </body>

        </html>";
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();
        $cleanCode = "";
        if ($info['code'] == 'sin-folio') {
            $cleanCode = $info['id'];
        } else {
            $cleanCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $info['code']);
        }
        $filePath = 'docs/' . $cleanCode . '.pdf';
        file_put_contents($filePath, $pdfOutput);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        echo $pdfOutput;
        exit; // Importante para evitar cualquier salida extra

    }

    public static function generatePrescriptionPDF(array $dataPDF)
    {
        $info = $dataPDF[0];
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $bootstrapCSS = file_get_contents('vendor/twbs/bootstrap/dist/css/bootstrap.min.css');

        // Función local para formatear valores
        function formatPrescriptionValue($value) {
            return $value ? htmlspecialchars($value) : 'N/A';
        }

        // Función para formatear fecha
        function formatPrescriptionDate($date) {
            if ($date) {
                return date('d/m/Y', strtotime($date));
            }
            return 'N/A';
        }

        // Función para formatear sexo
        function formatPrescriptionGender($sex) {
            return $sex == 1 ? 'Masculino' : ($sex == 2 ? 'Femenino' : 'N/A');
        }

        // Obtener logo del doctor o mostrar mensaje "Sin - Logo"
        $logoHtml = '';
        $hasCustomLogo = false;
        
        if (isset($info['doctor_id'])) {
            // Consultar el logo personalizado del doctor
            $doctorLogoQuery = "SELECT logo_path FROM users WHERE id = '" . mysqli_real_escape_string(\Utils\Helpers::connect(), $info['doctor_id']) . "' AND active = 1";
            $doctorLogoResult = \Utils\Helpers::connect()->query($doctorLogoQuery);
            
            if ($doctorLogoResult && $doctorLogoResult->num_rows > 0) {
                $doctorData = $doctorLogoResult->fetch_assoc();
                if (!empty($doctorData['logo_path']) && file_exists($doctorData['logo_path'])) {
                    // El doctor tiene logo personalizado y el archivo existe
                    $type = pathinfo($doctorData['logo_path'], PATHINFO_EXTENSION);
                    $data = file_get_contents($doctorData['logo_path']);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    
                    $logoHtml = "
                        <div class='logo-container'>
                            <img src='" . $base64 . "' alt='Logo Personal'>
                        </div>";
                    $hasCustomLogo = true;
                }
            }
        }
        
        // Si no tiene logo personalizado, mostrar mensaje "Sin - Logo"
        if (!$hasCustomLogo) {
            $logoHtml = "
                <div class='no-logo-container'>
                    <div class='no-logo-box'>
                        <span class='no-logo-text'>Sin - Logo</span>
                    </div>
                </div>";
        }

        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Receta Médica</title>
            <style>
                " . $bootstrapCSS . "
                body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
                .header { border-bottom: 2px solid #0066cc; margin-bottom: 20px; padding-bottom: 10px; }
                .section { margin-bottom: 15px; }
                .section-title { background-color: #f8f9fa; padding: 8px; border-left: 4px solid #0066cc; font-weight: bold; margin-bottom: 10px; }
                .info-table { width: 100%; border-collapse: collapse; }
                .info-table td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
                .info-table th { padding: 8px; border: 1px solid #ddd; background-color: #e3f2fd; font-weight: bold; text-align: left; }
                .prescription-content { background-color: #fafafa; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
                .signature-area { margin-top: 40px; text-align: center; }
                .signature-line { border-bottom: 1px solid #000; width: 200px; margin: 0 auto 5px; }
                .logo-container { max-width: 120px; max-height: 80px; overflow: hidden; }
                .logo-container img { max-width: 100%; max-height: 100%; object-fit: contain; }
                .no-logo-container { max-width: 120px; max-height: 80px; overflow: hidden; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0; border: 1px solid #ddd; border-radius: 5px; }
                .no-logo-box { padding: 10px; text-align: center; }
                .no-logo-text { font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <!-- Header con logo y título -->
            <div class='header'>
                <table style='width: 100%;'>
                    <tr>
                        <td style='width: 20%;'>
                            " . $logoHtml . "
                        </td>
                        <td style='width: 60%; text-align: center;'>
                            <h2 style='margin: 0; color: #0066cc;'>RECETA MÉDICA</h2>
                            <p style='margin: 5px 0; font-size: 14px;'>Fecha: " . formatPrescriptionDate($info['created_at']) . "</p>
                        </td>
                        <td style='width: 20%; text-align: right;'>
                            <p style='margin: 0; font-size: 10px;'>ID: " . formatPrescriptionValue($info['id']) . "</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Información del Doctor -->
            <div class='section'>
                <div class='section-title'>Información del Médico</div>
                <table class='info-table'>
                    <tr>
                        <th style='width: 25%;'>Nombre:</th>
                        <td style='width: 25%;'>" . formatPrescriptionValue($info['doctor_name'] . ' ' . $info['doctor_lastname']) . "</td>
                        <th style='width: 25%;'>Cédula Profesional:</th>
                        <td style='width: 25%;'>" . formatPrescriptionValue($info['doctor_professional_id']) . "</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>" . formatPrescriptionValue($info['doctor_email']) . "</td>
                        <th>Teléfono:</th>
                        <td>" . formatPrescriptionValue($info['doctor_phone']) . "</td>
                    </tr>
                </table>
            </div>

            <!-- Información del Paciente -->
            <div class='section'>
                <div class='section-title'>Información del Paciente</div>
                <table class='info-table'>
                    <tr>
                        <th style='width: 25%;'>Nombre:</th>
                        <td style='width: 25%;'>" . formatPrescriptionValue($info['patient_name'] . ' ' . $info['patient_lastname']) . "</td>
                        <th style='width: 25%;'>Fecha de Nacimiento:</th>
                        <td style='width: 25%;'>" . formatPrescriptionDate($info['patient_birthday']) . "</td>
                    </tr>
                    <tr>
                        <th>Edad:</th>
                        <td>" . formatPrescriptionValue($info['age']) . " años</td>
                        <th>Sexo:</th>
                        <td>" . formatPrescriptionGender($info['sex']) . "</td>
                    </tr>
                    <tr>
                        <th>Peso:</th>
                        <td>" . formatPrescriptionValue($info['weight']) . " kg</td>
                        <th>Altura:</th>
                        <td>" . formatPrescriptionValue($info['height']) . " cm</td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>" . formatPrescriptionValue($info['patient_phone']) . "</td>
                        <th>Email:</th>
                        <td>" . formatPrescriptionValue($info['patient_email']) . "</td>
                    </tr>
                </table>
            </div>

            <!-- Diagnóstico -->
            <div class='section'>
                <div class='section-title'>Diagnóstico</div>
                <div class='prescription-content'>
                    " . formatPrescriptionValue($info['diagnosis']) . "
                </div>
            </div>

            <!-- Medicamentos y Dosificación -->
            <div class='section'>
                <div class='section-title'>Medicamentos y Dosificación</div>
                <div class='prescription-content'>
                    " . nl2br(formatPrescriptionValue($info['medications_dosage'])) . "
                </div>
            </div>

            <!-- Indicaciones Especiales -->
            <div class='section'>
                <div class='section-title'>Indicaciones Especiales</div>
                <div class='prescription-content'>
                    " . nl2br(formatPrescriptionValue($info['special_indications'])) . "
                </div>
            </div>

            <!-- Próxima Cita -->
            <div class='section'>
                <div class='section-title'>Próxima Cita</div>
                <table class='info-table'>
                    <tr>
                        <th style='width: 25%;'>Fecha de Próxima Cita:</th>
                        <td>" . formatPrescriptionDate($info['next_date']) . "</td>
                    </tr>
                </table>
            </div>

            <!-- Área de Firma -->
            <div class='signature-area'>
                <div class='signature-line'></div>
                <p style='margin: 5px 0; font-weight: bold;'>Dr. " . formatPrescriptionValue($info['doctor_name'] . ' ' . $info['doctor_lastname']) . "</p>
                <p style='margin: 0; font-size: 10px;'>Cédula Profesional: " . formatPrescriptionValue($info['doctor_professional_id']) . "</p>
            </div>

            <!-- Footer -->
            <div style='margin-top: 30px; text-align: center; font-size: 10px; color: #666;'>
                <p>Esta receta fue generada electrónicamente el " . date('d/m/Y H:i:s') . "</p><br>
                <p>MEDILINK no se hace responsable por el uso que se le de a esta receta ni por los resultados que se obtengan, por lo que se recomienda seguir las indicaciones del médico.</p>
            </div>
        </body>
        </html>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();
        
        $cleanCode = "";
        if (isset($info['code']) && $info['code'] != 'sin-folio') {
            $cleanCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $info['code']);
        } else {
            $cleanCode = 'prescription_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $info['id']);
        }
        
        $filePath = 'docs/prescriptions/' . $cleanCode . '.pdf';
        file_put_contents($filePath, $pdfOutput);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        echo $pdfOutput;
        exit;
    }

    public static function generateCashCutPDF(array $info)
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $bootstrapCSS = file_get_contents('vendor/twbs/bootstrap/dist/css/bootstrap.min.css');
        $path = 'assets/images/axo-3.gif';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $fecha = date('d/m/Y H:i');
        $usuario = $info['usuario_corte'];
        $sucursal = $info['sucursal'];
        $movimientos = $info['movimientos'];
        $id_corte = $info['id_corte'];

        $total = 0;
        $tabla = "
            <style>
                th, td {
                    font-size: 12px;
                    padding: 6px !important;
                    vertical-align: middle !important;
                }
                .table-total {
                    font-weight: bold;
                    background-color: #f8f9fa;
                }
            </style>
            <table class='table table-bordered table-hover mt-4'>
                <thead class='table-dark text-center'>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Método de Pago</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($movimientos as $index => $m) {
            $total += $m['monto'];
            $tabla .= "
                    <tr>
                        <td class='text-center'>" . ($index + 1) . "</td>
                        <td>{$m['cliente']}</td>
                        <td>{$m['servicio']}</td>
                        <td>{$m['metodo_pago']}</td>
                        <td class='text-end'>$" . number_format($m['monto'], 2) . "</td>
                        <td>" . date('d/m/Y H:i', strtotime($m['fecha_pago'])) . "</td>
                    </tr>";
        }

        $tabla .= "
                <tr class='table-total'>
                    <td colspan='4' class='text-end'>Total</td>
                    <td class='text-end'>$" . number_format($total, 2) . "</td>
                    <td></td>
                </tr>";

        $tabla .= "</tbody></table>";


        $html = "
                <!DOCTYPE html>
                <html lang='es'>
                <head>
                    <meta charset='UTF-8'>
                    <title>Corte de Caja</title>
                    <style>{$bootstrapCSS}</style>
                </head>
                <body>
                    <table style='width: 100%; margin-bottom: 20px;'>
                        <tr>
                            <td style='width: 50%;'>
                                <img src='" . $base64 . "' width='200' height='200'>
                            </td>
                            <td style='width: 50%; text-align: right;'>
                                <p><strong>Fecha de generación:</strong> {$fecha}</p>
                                <p><strong>Sucursal:</strong> {$sucursal}</p>
                                <p><strong>Usuario:</strong> {$usuario}</p>
                                <p><strong>ID Corte:</strong> {$id_corte}</p>
                            </td>
                        </tr>
                    </table>
                    <h4 class='text-center'>Movimientos del Corte</h4>
                    {$tabla}
                </body>
                </html>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();
        $filePath = "docs/cashcuts/{$id_corte}.pdf";

        file_put_contents($filePath, $pdfOutput);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        echo $pdfOutput;
        exit;
    }

    public static function generateCashCutExcel(array $info)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $usuario = $info['usuario_corte'];
        $sucursal = $info['sucursal'];
        $movimientos = $info['movimientos'];
        $id_corte = $info['id_corte'];
        $fecha = date('d/m/Y H:i');

        // Encabezados del reporte
        $sheet->setCellValue('A1', 'Corte de Caja');
        $sheet->setCellValue('A2', "ID del Corte: $id_corte");
        $sheet->setCellValue('A3', "Usuario: $usuario");
        $sheet->setCellValue('A4', "Sucursal: $sucursal");
        $sheet->setCellValue('A5', "Fecha de generación: $fecha");

        // Encabezados de tabla
        $headers = ['#', 'Cliente', 'Servicio', 'Método de Pago', 'Monto', 'Fecha de Pago'];
        $sheet->fromArray($headers, null, 'A7');

        // Datos
        $rowIndex = 8;
        $total = 0;

        foreach ($movimientos as $index => $m) {
            $sheet->setCellValue("A$rowIndex", $index + 1);
            $sheet->setCellValue("B$rowIndex", $m['cliente']);
            $sheet->setCellValue("C$rowIndex", $m['servicio']);
            $sheet->setCellValue("D$rowIndex", $m['metodo_pago']);
            $sheet->setCellValue("E$rowIndex", $m['monto']);
            $sheet->setCellValue("F$rowIndex", date('d/m/Y H:i', strtotime($m['fecha_pago'])));
            $total += $m['monto'];
            $rowIndex++;
        }

        // Fila de total
        $sheet->setCellValue("D$rowIndex", 'Total');
        $sheet->setCellValue("E$rowIndex", $total);

        // Estilos básicos
        $sheet->getStyle("A7:F7")->getFont()->setBold(true);
        $sheet->getStyle("A7:F$rowIndex")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        // Nombre y guardado
        $filename = "docs/cashcuts/" . $id_corte . ".xlsx";

        if (ob_get_length()) ob_end_clean();

        // Cabeceras para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output'); // salida directa al navegador
        exit;
    }

    public static function exportCashCutsRangeToExcel(array $groupedCuts)
    {
        $spreadsheet = new Spreadsheet();

        $nombreSucursal = ''; // ← la tomaremos del primer corte
        foreach ($groupedCuts as $index => $corte) {
            $sheet = $index === 0
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet();

            $sheet->setTitle(substr($corte['id_corte'], 0, 20));

            // Guardar el nombre de la sucursal (solo el primero, todos son iguales)
            if ($index === 0) {
                $nombreSucursal = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $corte['sucursal']));
            }

            // Encabezados de corte
            $sheet->setCellValue('A1', 'ID del Corte: ' . $corte['id_corte']);
            $sheet->setCellValue('A2', 'Usuario: ' . $corte['usuario']);
            $sheet->setCellValue('A3', 'Sucursal: ' . $corte['sucursal']);
            $sheet->setCellValue('A4', 'Fecha de Inicio: ' . $corte['start_date']);
            $sheet->setCellValue('A5', 'Fecha de Fin: ' . $corte['end_date']);

            // Encabezado tabla
            $headers = ['#', 'Cliente', 'Servicio', 'Método de Pago', 'Monto', 'Fecha de Pago'];
            $sheet->fromArray($headers, null, 'A7');

            $row = 8;
            $total = 0;
            foreach ($corte['pagos'] as $i => $pago) {
                $sheet->setCellValue("A$row", $i + 1);
                $sheet->setCellValue("B$row", $pago['cliente']);
                $sheet->setCellValue("C$row", $pago['servicio']);
                $sheet->setCellValue("D$row", $pago['metodo_pago']);
                $sheet->setCellValue("E$row", $pago['monto']);
                $sheet->setCellValue("F$row", date('d/m/Y H:i', strtotime($pago['fecha_pago'])));
                $total += $pago['monto'];
                $row++;
            }

            // Total
            $sheet->setCellValue("D$row", 'Total');
            $sheet->setCellValue("E$row", $total);

            // Estilos
            $sheet->getStyle("A7:F7")->getFont()->setBold(true);
            $sheet->getStyle("A7:F$row")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        // Crear nombre y ruta con sucursal
        $fileName = "cortes_{$nombreSucursal}_" . date('Ymd_His') . '.xlsx';
        $filePath = 'docs/cashcuts/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        readfile($filePath);
        exit;
    }
}
