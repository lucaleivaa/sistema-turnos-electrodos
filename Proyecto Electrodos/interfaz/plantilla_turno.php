<?php
function generarZPL($numero, $motivo, $fecha) {
    // Mapeo de motivos a textos legibles
    $motivosTexto = [
        'compra' => 'COMPRA',
        'retiro' => 'RETIRO',
        'asistencia' => 'ASISTENCIA TEC.',
        'presupuesto' => 'PRESUPUESTO'
    ];
    
    $motivoImpresion = $motivosTexto[$motivo] ?? strtoupper($motivo);
    
    // Código ZPL optimizado para Zebra ZD220
    $zpl = "^XA
^PW800
^LL640
^LS0

^FO50,30^A0N,60,60^FDElectrodos^FS
^FO50,100^GB700,3,3^FS

^FO50,140^A0N,40,40^FDTurno:^FS
^FO50,200^A0N,160,160^FD{$numero}^FS

^FO50,380^A0N,36,36^FDMotivo: {$motivoImpresion}^FS
^FO50,430^A0N,30,30^FD{$fecha}^FS

^XZ
";
    
    return $zpl;
}
?>
