<template>
    <Dialog v-model:visible="dialogVisible" modal header="Comprobante de Pago" :style="{ width: '60vw' }"
        @hide="handleClose">
        <div v-if="loading" class="flex justify-center p-4">
            <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
        </div>
        <div v-else-if="error" class="p-4 text-center text-red-600">
            <p>{{ error }}</p>
            <Button label="Reintentar" @click="cargarDatosPago" class="mt-2" />
        </div>
        <div v-else class="flex flex-col gap-4">
            <iframe :src="localPdfUrl" width="100%" height="700px" frameborder="0"></iframe>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import axios from 'axios';
import jsPDF from 'jspdf';

const props = defineProps({
    prestamosId: {
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
const error = ref('');
const localPdfUrl = ref('');
const pagoData = ref(null);

watch(() => props.visible, (newValue) => {
    dialogVisible.value = newValue;
    if (newValue && props.prestamosId) {
        cargarDatosPago();
    }
});

watch(() => dialogVisible.value, (newValue) => {
    emit('update:visible', newValue);
    if (!newValue) {
        emit('close');
        // Limpiar URL del PDF cuando se cierre el diálogo
        if (localPdfUrl.value) {
            URL.revokeObjectURL(localPdfUrl.value);
            localPdfUrl.value = '';
        }
    }
});

const handleClose = () => {
    emit('close');
};

const cargarDatosPago = async () => {
    try {
        loading.value = true;
        error.value = '';
        
        const response = await axios.get(`/pago/cuota/${props.prestamosId}`);
        
        if (response.data && response.data.data && response.data.data.length > 0) {
            pagoData.value = response.data.data[0]; // Tomar el primer elemento
            await generatePDF();
        } else {
            error.value = 'No se encontraron datos de pago para este préstamo';
        }
    } catch (error) {
        console.error('Error al cargar datos del pago:', error);
        error.value = 'Error al cargar los datos del pago. Por favor, inténtelo nuevamente.';
    } finally {
        loading.value = false;
    }
};

const generatePDF = async () => {
    try {
        if (!pagoData.value) {
            console.error("No hay datos de pago disponibles");
            return;
        }

        const data = pagoData.value;
        
        // Configuración inicial del PDF
        const pdf = new jsPDF({ unit: "mm", format: "a4" });
        const margin = 15;
        const pageWidth = 210;
        const pageHeight = pdf.internal.pageSize.getHeight();
        const centerX = pageWidth / 2;
        let y = 20;

        // Información de fecha y hora
        const now = new Date();
        const fechaActual = now.toLocaleDateString("es-PE", {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        const horaActual = now.toLocaleTimeString("es-PE", {
            hour: '2-digit',
            minute: '2-digit'
        });

        // Número de referencia único
        const referenceNumber = data.referencia;

        // Función para agregar encabezado de empresa
        const addCompanyHeader = () => {
            // Logo placeholder o nombre de empresa
            pdf.setFontSize(20);
            pdf.setFont("helvetica", "bold");
            pdf.setTextColor(41, 128, 185); // Azul corporativo
            pdf.text("FINANCIERA XYZ", centerX, y, { align: "center" });
            y += 6;
            
            pdf.setFontSize(10);
            pdf.setFont("helvetica", "normal");
            pdf.setTextColor(100, 100, 100);
            pdf.text("Sistema de Gestión de Préstamos", centerX, y, { align: "center" });
            y += 4;
            pdf.text("RUC: 20123456789 | Teléfono: (01) 234-5678", centerX, y, { align: "center" });
            y += 8;
        };

        // Función para agregar marca de agua
        const addWatermark = (text) => {
            pdf.saveGraphicsState();
            pdf.setGState(new pdf.GState({ opacity: 0.1 }));
            pdf.setFontSize(50);
            pdf.setTextColor(150, 150, 150);
            pdf.text(text, centerX, pageHeight / 2, {
                align: 'center',
                angle: 45
            });
            pdf.restoreGraphicsState();
        };

        // Función para agregar pie de página
        const addFooter = () => {
            const footerY = pageHeight - 15;
            pdf.setFontSize(8);
            pdf.setTextColor(100, 100, 100);
            
            // Línea divisoria
            pdf.setLineWidth(0.1);
            pdf.line(margin, footerY - 5, pageWidth - margin, footerY - 5);
            
            pdf.text(`Generado el ${fechaActual} a las ${horaActual}`, margin, footerY);
            pdf.text(`Ref: ${referenceNumber}`, centerX, footerY, { align: "center" });
            pdf.text("Este documento es válido sin firma", pageWidth - margin, footerY, { align: "right" });
        };

        // Generar encabezado
        addCompanyHeader();

        // Título del documento
        pdf.setFontSize(18);
        pdf.setFont("helvetica", "bold");
        pdf.setTextColor(0, 0, 0);
        pdf.text("COMPROBANTE DE PAGO", centerX, y, { align: "center" });
        y += 8;

        // Información del recibo
        pdf.setFontSize(10);
        pdf.setFont("helvetica", "normal");
        pdf.text(`N° de Recibo: ${referenceNumber}`, centerX, y, { align: "center" });
        y += 6;

        // Línea divisoria principal
        pdf.setLineWidth(0.5);
        pdf.setDrawColor(41, 128, 185);
        pdf.line(margin, y, pageWidth - margin, y);
        y += 10;

        // Sección de información del cliente
        pdf.setFillColor(248, 249, 250);
        pdf.rect(margin, y, pageWidth - 2 * margin, 35, 'F');
        
        y += 6;
        pdf.setFontSize(12);
        pdf.setFont("helvetica", "bold");
        pdf.setTextColor(41, 128, 185);
        pdf.text("INFORMACIÓN DEL CLIENTE", margin + 5, y);
        y += 8;

        pdf.setFontSize(10);
        pdf.setFont("helvetica", "normal");
        pdf.setTextColor(0, 0, 0);

        const col1X = margin + 5;
        const col2X = centerX + 10;
        const lineHeight = 6;

        // Información del cliente - Columna 1
        pdf.setFont("helvetica", "bold");
        pdf.text("Nombre:", col1X, y);
        pdf.setFont("helvetica", "normal");
        pdf.text(data.cliente_nom_ape || 'N/A', col1X + 25, y);
        y += lineHeight;

        pdf.setFont("helvetica", "bold");
        pdf.text("DNI:", col1X, y);
        pdf.setFont("helvetica", "normal");
        pdf.text(data.cliente_Dni || 'N/A', col1X + 25, y);

        // Información del cliente - Columna 2
        y -= lineHeight;
        pdf.setFont("helvetica", "bold");
        pdf.text("Teléfono:", col2X, y);
        pdf.setFont("helvetica", "normal");
        pdf.text(data.CLiente_Telefono || 'N/A', col2X + 25, y);
        y += lineHeight;

        pdf.setFont("helvetica", "bold");
        pdf.text("Dirección:", col2X, y);
        pdf.setFont("helvetica", "normal");
        // Manejar dirección larga
        const direccion = data.CLiente_Direccion || 'N/A';
        const maxWidth = 60;
        const splitDireccion = pdf.splitTextToSize(direccion, maxWidth);
        pdf.text(splitDireccion, col2X + 25, y);
        
        y += 15;

        // Sección de información del pago
        pdf.setFillColor(41, 128, 185);
        pdf.rect(margin, y, pageWidth - 2 * margin, 8, 'F');
        
        pdf.setFontSize(12);
        pdf.setFont("helvetica", "bold");
        pdf.setTextColor(255, 255, 255);
        pdf.text("DETALLE DEL PAGO", centerX, y + 5, { align: "center" });
        y += 15;

        // Tabla de detalles del pago
        const tableY = y;
        const tableHeight = 60;
        
        // Encabezados de tabla
        pdf.setFillColor(240, 240, 240);
        pdf.rect(margin, tableY, pageWidth - 2 * margin, 8, 'F');
        
        pdf.setFontSize(10);
        pdf.setFont("helvetica", "bold");
        pdf.setTextColor(0, 0, 0);
        
        const headers = [
            { text: "Concepto", x: margin + 5, width: 60 },
            { text: "Fecha de Pago", x: margin + 45, width: 40 },
            { text: "Capital", x: margin + 90, width: 25 },
            { text: "Interés", x: margin + 120, width: 25 },
            { text: "Total", x: margin + 155, width: 25 }
        ];
        
        headers.forEach(header => {
            pdf.text(header.text, header.x, tableY + 5);
        });
        
        // Línea debajo de encabezados
        pdf.setLineWidth(0.1);
        pdf.line(margin, tableY + 8, pageWidth - margin, tableY + 8);
        
        // Datos de la tabla
        y = tableY + 15;
        pdf.setFont("helvetica", "normal");
        
        pdf.text(`Cuota N° ${data.numero_cuota}`, margin + 5, y);
        pdf.text(data.fecha_pago, margin + 48, y);
        pdf.text(`S/. ${parseFloat(data.monto_capital).toFixed(2)}`, margin + 103, y, { align: "right" });
        pdf.text(`S/. ${parseFloat(data.monto_interes).toFixed(2)}`, margin + 133, y, { align: "right" });
        pdf.text(`S/. ${parseFloat(data.monto_total).toFixed(2)}`, margin + 165, y, { align: "right" });
        
        // Línea debajo de datos
        y += 8;
        pdf.setLineWidth(0.1);
        pdf.line(margin, y, pageWidth - margin, y);
        
        // Total destacado
        y += 10;
        pdf.setFillColor(252, 248, 227);
        pdf.rect(margin + 120, y, 75, 12, 'F');
        
        pdf.setFontSize(10);
        pdf.setFont("helvetica", "bold");
        pdf.text("TOTAL PAGADO:", margin + 125, y + 8);
        pdf.setTextColor(220, 53, 69);
        pdf.text(`S/. ${parseFloat(data.monto_total).toFixed(2)}`, pageWidth - margin - 5, y + 8, { align: "right" });
        
        y += 100;


        // Nota final
        pdf.setFontSize(8);
        pdf.setTextColor(150, 150, 150);
        const nota = "Este comprobante certifica el pago realizado. Conserve este documento para sus registros.";
        pdf.text(nota, centerX, y, { align: "center" });

        // Agregar marca de agua y pie de página
        addWatermark("PAGADO");
        addFooter();

        // Generar URL del PDF
        const pdfBlob = pdf.output("blob");
        if (localPdfUrl.value) {
            URL.revokeObjectURL(localPdfUrl.value);
        }
        localPdfUrl.value = URL.createObjectURL(pdfBlob);

    } catch (error) {
        console.error("Error al generar PDF:", error);
        error.value = "Error al generar el PDF del comprobante";
    }
};

onMounted(async () => {
    if (props.prestamosId && props.visible) {
        await cargarDatosPago();
    }
});

watch(() => props.prestamosId, async (newId) => {
    if (newId && props.visible) {
        await cargarDatosPago();
    }
});
</script>