<template>
    <Dialog v-model:visible="dialogVisible" modal header="Recibo de pago" :style="{ width: '50vw' }"
        @hide="handleClose">
        <div v-if="loading" class="flex justify-center p-4">
            <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
        </div>
        <iframe v-else :src="localPdfUrl" width="100%" height="700px"></iframe>
    </Dialog>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import axios from 'axios';
import jsPDF from 'jspdf';

const props = defineProps({
    comprobanteId: {
        type: [String, Number],
        required: true
    },
    visible: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['update:visible', 'close']);
const dialogVisible = ref(props.visible);
const loading = ref(true);
const localPdfUrl = ref('');
const prestamoData = ref(null);

watch(() => props.visible, (newValue) => {
    dialogVisible.value = newValue;
    if (newValue && props.comprobanteId) {
        cargarDatosPagos();
    }
});

watch(() => dialogVisible.value, (newValue) => {
    emit('update:visible', newValue);
    if (!newValue) {
        emit('close');
    }
});

const handleClose = () => {
    emit('close');
};

const cargarDatosPagos = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/pago/cuota/${props.comprobanteId}`);
        prestamoData.value = response.data;
        await generatePDF();
    } catch (error) {
        console.error('Error al cargar datos del préstamo:', error);
    } finally {
        loading.value = false;
    }
};

const generarComprobantePago = async (dataPago) => {
    try {
        // Validación más flexible para manejar diferentes estructuras de datos
        let pago;
        
        if (dataPago && dataPago.data && Array.isArray(dataPago.data) && dataPago.data.length > 0) {
            // Formato: { data: [...] }
            pago = dataPago.data[0];
        } else if (dataPago && Array.isArray(dataPago) && dataPago.length > 0) {
            // Formato: [...]
            pago = dataPago[0];
        } else if (dataPago && typeof dataPago === 'object' && !Array.isArray(dataPago)) {
            // Formato: { ... } (objeto directo)
            pago = dataPago;
        } else {
            console.error("No hay datos de pago disponibles o formato incorrecto", dataPago);
            return { success: false, error: "No hay datos disponibles" };
        }
        
        // Verificamos que tenga las propiedades mínimas necesarias
        if (!pago.cliente_Dni || !pago.cliente_nom_ape) {
            console.error("Datos de pago incompletos", pago);
            return { success: false, error: "Datos de pago incompletos" };
        }
        
        // Configuración inicial del PDF
        const pdf = new jsPDF({ unit: "mm", format: "a4" });
        const margin = 10;
        const pageWidth = 210;
        const contentWidth = pageWidth - (margin * 2);
        const pageHeight = pdf.internal.pageSize.getHeight();
        const centerX = pageWidth / 2;
        let y = 15; // Margen superior

        // Información de fecha y hora actual
        const now = new Date();
        const fechaActual = now.toLocaleDateString("es-PE");
        const horaActual = now.toLocaleTimeString("es-PE");

        // Generador de número de referencia único para el comprobante
        const comprobantNumber = `CP-${now.getFullYear()}${(now.getMonth() + 1).toString().padStart(2, '0')}${now.getDate().toString().padStart(2, '0')}-${Math.floor(1000 + Math.random() * 9000)}`;

        // Función para agregar marca de agua
        const addWatermark = (text) => {
            pdf.saveGraphicsState();
            pdf.setGState(new pdf.GState({opacity: 0.3}));
            pdf.setFontSize(40);
            pdf.setTextColor(150, 150, 150);
            pdf.text(text, centerX, pageHeight / 2, {
                align: 'center',
                angle: 45
            });
            pdf.restoreGraphicsState();
        };

        // Función para agregar pie de página
        const addFooter = () => {
            pdf.setFontSize(8);
            pdf.setTextColor(100, 100, 100);
            pdf.text(`Comprobante generado el ${fechaActual} a las ${horaActual}`, centerX, pageHeight - 10, { align: "center" });
            pdf.text(`Ref: ${comprobantNumber}`, centerX, pageHeight - 7, { align: "center" });
        };

        // Título del comprobante
        pdf.setFontSize(16);
        pdf.setFont("helvetica", "bold");
        pdf.setTextColor(0, 0, 0);
        pdf.text("COMPROBANTE DE PAGO", centerX, y, { align: "center" });
        y += 10;

        // Fecha, hora y número de referencia
        pdf.setFontSize(9);
        pdf.setFont("helvetica", "normal");
        pdf.text(`Fecha: ${fechaActual}    Hora: ${horaActual}`, centerX, y, { align: "center" });
        y += 5;
        pdf.text(`N° de Comprobante: ${comprobantNumber}`, centerX, y, { align: "center" });
        y += 8;

        // Línea divisoria
        pdf.setLineWidth(0.3);
        pdf.line(margin, y, pageWidth - margin, y);
        y += 8;

        // Información del Cliente
        pdf.setFontSize(10);
        pdf.setFont("helvetica", "bold");
        pdf.text("DATOS DEL CLIENTE", margin, y);
        y += 6;

        pdf.setFontSize(9);
        pdf.setFont("helvetica", "normal");
        pdf.text(`DNI: ${pago.cliente_Dni}`, margin, y);
        y += 5;
        pdf.text(`Nombre: ${pago.cliente_nom_ape}`, margin, y);
        y += 8;

        // Información del Préstamo
        pdf.setFontSize(10);
        pdf.setFont("helvetica", "bold");
        pdf.text("DATOS DEL PRÉSTAMO", margin, y);
        y += 6;

        pdf.setFontSize(9);
        pdf.setFont("helvetica", "normal");
        pdf.text(`ID Préstamo: ${pago.prestamo_id || 'N/A'}`, margin, y);
        pdf.text(`Capital total: S/ ${pago.capital || '0.00'}`, pageWidth - margin, y, { align: "right" });
        y += 5;
        pdf.text(`Fecha de pago: ${pago.fecha_pago || 'N/A'}`, margin, y);
        pdf.text(`ID Cuota: ${pago.cuota_id || 'N/A'}`, pageWidth - margin, y, { align: "right" });
        y += 8;

        // Información del Pago
        pdf.setFontSize(10);
        pdf.setFont("helvetica", "bold");
        pdf.text("DETALLE DEL PAGO", margin, y);
        y += 8;

        // Tabla de detalles
        const startY = y;
        
        // Cabecera de tabla
        pdf.setFillColor(240, 240, 240);
        pdf.rect(margin, y - 5, contentWidth, 7, 'F');
        pdf.setFont("helvetica", "bold");
        pdf.setFontSize(8);
        pdf.text("CONCEPTO", margin + 5, y);
        pdf.text("MONTO (S/)", pageWidth - margin - 5, y, { align: "right" });
        y += 5;

        // Contenido de la tabla
        pdf.setFont("helvetica", "normal");
        pdf.text("Capital", margin + 5, y);
        pdf.text(pago.monto_capital || '0.00', pageWidth - margin - 5, y, { align: "right" });
        y += 5;
        
        pdf.text("Interés", margin + 5, y);
        pdf.text(pago.monto_interes || '0.00', pageWidth - margin - 5, y, { align: "right" });
        y += 5;
        
        // Línea divisoria antes del total
        pdf.setLineWidth(0.2);
        pdf.line(margin, y, pageWidth - margin, y);
        y += 5;
        
        // Total
        pdf.setFont("helvetica", "bold");
        pdf.text("TOTAL PAGADO", margin + 5, y);
        pdf.text(pago.monto_total || '0.00', pageWidth - margin - 5, y, { align: "right" });
        
        // Borde de la tabla
        pdf.setLineWidth(0.3);
        pdf.rect(margin, startY - 5, contentWidth, y - startY + 5);
        y += 15;

        // Estado del pago
        pdf.setFontSize(10);
        const estadoTexto = pago.Estado === 1 ? "PAGADO" : "PENDIENTE";
        const colorEstado = pago.Estado === 1 ? [0, 128, 0] : [255, 0, 0]; // Verde si pagado, rojo si pendiente
        
        pdf.setTextColor(...colorEstado);
        pdf.setFont("helvetica", "bold");
        pdf.text(`ESTADO: ${estadoTexto}`, centerX, y, { align: "center" });
        y += 15;
        
        // Restaurar color de texto
        pdf.setTextColor(0, 0, 0);

        // Sección de firmas
        pdf.setFontSize(9);
        pdf.setFont("helvetica", "normal");
        pdf.text("______________________________", margin + 30, y, { align: "center" });
        pdf.text("______________________________", pageWidth - margin - 30, y, { align: "center" });
        y += 5;
        pdf.text("Firma del Cliente", margin + 30, y, { align: "center" });
        pdf.text("Firma del Responsable", pageWidth - margin - 30, y, { align: "center" });
        y += 20;

        // Nota legal
        pdf.setFontSize(7);
        pdf.setTextColor(100, 100, 100);
        pdf.text("Este comprobante es válido como constancia de pago. Conserve este documento para futuras referencias.", 
            centerX, y, { align: "center", maxWidth: contentWidth });

        // Agregar marca de agua y pie de página
        addWatermark("ORIGINAL");
        addFooter();

        // Generar URL del PDF
        const pdfUrl = pdf.output("bloburl");
        
        console.log("Comprobante de pago generado correctamente:", comprobantNumber);
        return { 
            success: true, 
            comprobantNumber,
            pdfUrl 
        };
    } catch (error) {
        console.error("Error al generar comprobante:", error);
        return { success: false, error: error.message };
    }
};

// Función para integrar con tu carga de datos existente
const generatePDF = async () => {
    try {
        if (!prestamoData.value) {
            console.error("No hay datos de préstamo disponibles");
            return { success: false, error: "No hay datos disponibles" };
        }
        
        // Llamamos a la función de generación del comprobante pasando los datos directamente
        const resultado = await generarComprobantePago(prestamoData.value);
        
        if (resultado.success) {
            console.log("Comprobante generado con éxito, referencia:", resultado.comprobantNumber);
            // AQUÍ ESTÁ LA CORRECCIÓN: Asignamos la URL del PDF a localPdfUrl
            localPdfUrl.value = resultado.pdfUrl;
            return resultado;
        } else {
            console.error("Error al generar comprobante:", resultado.error);
            return resultado;
        }
    } catch (error) {
        console.error("Error al generar PDF:", error);
        return { success: false, error: error.message };
    }
};
</script>