<template>
    <Dialog v-model:visible="dialogVisible" modal header="Talonario de Cuotas" :style="{ width: '50vw' }"
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
const localPdfUrl = ref('');
const prestamoData = ref(null);

watch(() => props.visible, (newValue) => {
    dialogVisible.value = newValue;
    if (newValue && props.prestamosId) {
        cargarDatosPrestamo();
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

const cargarDatosPrestamo = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/prestamo/${props.prestamosId}/Talonario/cutas`);
        prestamoData.value = response.data;
        await generatePDF();
    } catch (error) {
        console.error('Error al cargar datos del préstamo:', error);
    } finally {
        loading.value = false;
    }
};
const generatePDF = async () => {
    try {
        if (!prestamoData.value) {
            console.error("No hay datos de préstamo disponibles");
            return;
        }

        const data = prestamoData.value;
        const cliente = data.cliente;
        const cuotas = data.cuotas;

        // Configuración inicial del PDF
        const pdf = new jsPDF({ unit: "mm", format: "a4" });
        const margin = 10;
        const pageWidth = 210;
        const pageHeight = pdf.internal.pageSize.getHeight();
        const centerX = pageWidth / 2;
        let y = 15; // Aumentar el margen superior para el encabezado

        // Información de fecha y hora
        const now = new Date();
        const fechaActual = now.toLocaleDateString("es-PE");
        const horaActual = now.toLocaleTimeString("es-PE");

        // Generador de número de referencia único
        const referenceNumber = cliente.referencia;

        // Función para agregar marca de agua
        const addWatermark = (text) => {
            const watermarkPages = pdf.internal.getNumberOfPages();
            for (let i = 1; i <= watermarkPages; i++) {
                pdf.setPage(i);
                pdf.saveGraphicsState();
                pdf.setGState(new pdf.GState({opacity: 0.3}));
                pdf.setFontSize(40);
                pdf.setTextColor(150, 150, 150);
                pdf.text(text, centerX, pageHeight / 2, {
                    align: 'center',
                    angle: 45
                });
                pdf.restoreGraphicsState();
            }
        };

        // Función para agregar pie de página
        const addFooter = (currentPage, totalPages) => {
            pdf.setFontSize(8);
            pdf.setTextColor(100, 100, 100);
            pdf.text(window.location.href, pageWidth - margin, 287, { align: "right" });
            pdf.text(`Página ${currentPage} de ${totalPages}`, margin, 287, { align: "left" });
            pdf.text(`Ref: ${referenceNumber}`, centerX, 287, { align: "center" });
        };

        // Función para formatear moneda
        const formatCurrency = (value) => {
            if (!value) return "S/ 0.00";
            const numValue = parseFloat(value);
            return isNaN(numValue) ? "S/ 0.00" : `S/ ${numValue.toFixed(2)}`;
        };

        // Función para dividir texto largo
        const split = (text, width) => pdf.splitTextToSize(text || "N/A", width);

        // Espacio para logo (comentado hasta tener uno)
        /*
        if (logoBase64) {
            pdf.addImage(logoBase64, 'PNG', margin, y, 40, 15);
        }
        */

        // Encabezado del documento
        pdf.setFontSize(16);
        pdf.setFont("helvetica", "bold");
        pdf.setTextColor(0, 0, 0);
        pdf.text("CONTRATO DE PRÉSTAMO", centerX, y, { align: "center" });
        y += 10;

        // Fecha, hora y número de referencia
        pdf.setFontSize(7);
        pdf.setFont("helvetica", "normal");
        pdf.text(`Fecha: ${fechaActual}    Hora: ${horaActual}    Ref: ${referenceNumber}`, centerX, y, { align: "center" });
        y += 5;

        // Título del préstamo
        pdf.setFontSize(10);
        pdf.setFont("helvetica", "bold");
        pdf.text("DATOS DEL PRÉSTAMO", centerX, y, { align: "center" });
        pdf.setFont("helvetica", "normal");
        pdf.setFontSize(8);
        pdf.text("PRÉSTAMO CON TASA DE INTERÉS DIARIA DESDE EL PRIMER DÍA", centerX, y + 5, { align: "center" });
        y += 10;

        // Línea divisoria
        pdf.setLineWidth(0.3);
        pdf.line(margin, y, pageWidth - margin, y);
        y += 5;

        // Sección de información del cliente
        pdf.setFontSize(9);
        pdf.setFont("helvetica", "bold");
        pdf.text("Información del Cliente", centerX, y, { align: "center" });
        y += 2;
        pdf.setLineWidth(0.1);
        pdf.line(margin, y + 4, pageWidth - margin, y + 4);
        y += 8;

        pdf.setFontSize(8);
        pdf.setFont("helvetica", "normal");

        const col1 = margin;
        const col2 = centerX - 25;
        const col3 = centerX + 30;
        const maxW = 50;

        // Datos del cliente
        pdf.setFont("helvetica", "bold"); pdf.text("Nombre:", col1, y);
        pdf.setFont("helvetica", "normal"); const nombreLines = split(cliente.nombre, maxW);
        pdf.text(nombreLines, col1 + 20, y);

        pdf.setFont("helvetica", "bold"); pdf.text("DNI:", col2, y);
        pdf.setFont("helvetica", "normal"); const dniLines = split(cliente.dni, maxW);
        pdf.text(dniLines, col2 + 15, y);

        pdf.setFont("helvetica", "bold"); pdf.text("Fecha Inicio:", col3, y);
        pdf.setFont("helvetica", "normal"); const inicioLines = split(cliente.Fecha_Inicio, maxW);
        pdf.text(inicioLines, col3 + 22, y);

        let max1 = Math.max(nombreLines.length, dniLines.length, inicioLines.length);
        y += max1 * 5;

        // Segunda fila de datos del cliente
        pdf.setFont("helvetica", "bold"); pdf.text("Fecha Vencimiento:", col1, y);
        pdf.setFont("helvetica", "normal"); const vencLines = split(cliente.Fecha_Vencimiento, maxW);
        pdf.text(vencLines, col1 + 35, y);

        pdf.setFont("helvetica", "bold"); pdf.text("Capital:", col2, y);
        pdf.setFont("helvetica", "normal"); const capitalLines = split(`S/ ${cliente.capital}`, maxW);
        pdf.text(capitalLines, col2 + 20, y);

        pdf.setFont("helvetica", "bold"); pdf.text("Tasa diaria:", col3, y);
        pdf.setFont("helvetica", "normal"); const tasaLines = split(`${cliente.tasa_interes_diario}%`, maxW);
        pdf.text(tasaLines, col3 + 20, y);

        let max2 = Math.max(vencLines.length, capitalLines.length, tasaLines.length);
        y += max2 * 5;

        // Línea divisoria
        pdf.setLineWidth(0.3);
        pdf.line(margin, y, pageWidth - margin, y);
        y += 5;

        // Sección de información del aval
        pdf.setFontSize(9);
        pdf.setFont("helvetica", "bold");
        pdf.text("Información del Aval", centerX, y, { align: "center" });
        y += 2;
        pdf.setLineWidth(0.1);
        pdf.line(margin, y + 4, pageWidth - margin, y + 4);
        y += 8;

        // Datos del aval
        pdf.setFontSize(8);
        pdf.setFont("helvetica", "bold"); pdf.text("Nombre:", col1, y);
        pdf.setFont("helvetica", "normal"); const avalNombreLines = split(cliente.recomendado || "No especificado", maxW);
        pdf.text(avalNombreLines, col1 + 20, y);

        pdf.setFont("helvetica", "bold"); pdf.text("DNI:", col2, y);
        pdf.setFont("helvetica", "normal"); const avalDniLines = split(cliente.Dnirecomendado || "No especificado", maxW);
        pdf.text(avalDniLines, col2 + 15, y);

        pdf.setFont("helvetica", "bold"); pdf.text("Hora Impresa:", col3, y);
        pdf.setFont("helvetica", "normal"); pdf.text(horaActual, col3 + 25, y);

        let max3 = Math.max(avalNombreLines.length, avalDniLines.length);
        y += max3 * 5;

        // Línea divisoria
        pdf.setLineWidth(0.3);
        pdf.line(margin, y, pageWidth - margin, y);
        y += 5;

        // Sección de cronograma de pagos
        pdf.setFontSize(9);
        pdf.setFont("helvetica", "bold");
        pdf.text("Cronograma de Pagos", centerX, y, { align: "center" });
        y += 2;
        pdf.setLineWidth(0.1);
        pdf.line(margin, y + 4, pageWidth - margin, y + 4);
        y += 8;

        // Encabezados de la tabla
        const headers = [
            "N°", "Capital", "Inicio", "Vencimiento", "Días",
            "Tasa", "Interés", "Pago", "Saldo", "Total", "Estado"
        ];
        const colWidths = [16, 20, 21, 21, 11, 13, 14, 19, 17, 17, 19];

        pdf.setFontSize(8);
        pdf.setFont("helvetica", "bold");
        let x = margin;
        headers.forEach((header, i) => {
            pdf.text(header, x + colWidths[i] / 2, y, { align: "center" });
            x += colWidths[i];
        });

        y += 4;
        pdf.line(margin, y, pageWidth - margin, y);
        y += 4;

        // Filas de la tabla
        pdf.setFont("helvetica", "normal");
        let rowColor = false; // Alternating row colors
        
        cuotas.forEach((item) => {
            
            // Datos de la fila
            const rowData = [
                item.numero_cuota,
                `S/ ${item.capital}`,
                item.fecha_inicio,
                item.fecha_vencimiento,
                item.dias,
                `${item.interes}%`,
                `S/ ${item.monto_interes_pagar}`,
                `S/ ${item.monto_capital_pagar}`,
                `S/ ${item.saldo_capital}`,
                `S/ ${item.monto_total_pagar}`,
                item.estado,
            ];

            // Procesamiento de líneas de texto
            const cellLines = rowData.map((data, i) =>
                pdf.splitTextToSize(data?.toString() || "", colWidths[i] - 2)
            );
            const maxLines = Math.max(...cellLines.map(lines => lines.length));

            // Agregar nueva página si es necesario
            if (y + maxLines * 5 > pageHeight - margin - 20) {
                pdf.addPage();
                y = margin + 10;

                pdf.setFontSize(6.5);
                pdf.setFont("helvetica", "bold");
                let headerX = margin;
                headers.forEach((header, i) => {
                    pdf.text(header, headerX + colWidths[i] / 2, y, { align: "center" });
                    headerX += colWidths[i];
                });

                y += 4;
                pdf.line(margin, y, pageWidth - margin, y);
                y += 4;
                rowColor = false;
            }

            // Fondo de fila alternante (gris claro)
            if (rowColor) {
                pdf.setFillColor(245, 245, 245);
                pdf.rect(margin, y - 2, pageWidth - (margin * 2), maxLines * 5, 'F');
            }
            rowColor = !rowColor;

            // Texto de la celda
            pdf.setFont("helvetica", "normal");
            cellLines.forEach((lines, index) => {
                lines.forEach((line, lineIndex) => {
                    // Usar colores destacados para los diferentes estados
                    if (index === 10 && line.toLowerCase() === "pagado") {
                        pdf.setTextColor(0, 100, 0); // Verde oscuro para "Pagado"
                    } else if (index === 10 && line.toLowerCase() === "pendiente") {
                        pdf.setTextColor(150, 0, 0); // Rojo para "Pendiente"
                    } else if (index === 10 && line.toLowerCase() === "parcial") {
                        pdf.setTextColor(255, 165, 0); // Naranja para "Parcial"
                    } else {
                        pdf.setTextColor(0, 0, 0); // Negro para el resto
                    }

                    pdf.text(line, margin + colWidths.slice(0, index).reduce((a, b) => a + b, 0) + colWidths[index] / 2, y + lineIndex * 5, {
                        align: "center"
                    });
                });
            });
            pdf.setTextColor(0, 0, 0); // Restaurar color por defecto

            y += maxLines * 5;
        });

        // Línea final de la tabla
        pdf.line(margin, y, pageWidth - margin, y);
        pdf.setLineWidth(0.3).line(margin, y, pageWidth - margin, y);
        y += 5;

        // Sección de notas importantes
        pdf.setFont("helvetica", "bold").setFontSize(9);
        pdf.text("Importante:", margin, y);
        y += 6;
        pdf.setFont("helvetica", "normal").setFontSize(8);
        const notas = [
            "La fecha de vencimiento se definirá al momento del pago. La nueva cuota inicia el mismo día en que se termina de pagar la anterior.",
            "El monto del préstamo puede variar desde el momento de la solicitud.",
            "Si han transcurrido menos de 15 días, el sistema considerará como si fueran 15 días.",
            "Si han pasado entre 16 y 30 días, y se paga el total de la cuota (capital o solo intereses), se tomará como si hubieran transcurrido 30 días exactos.",
            "Si pasan más de 35 días sin pago completo, se aplicarán intereses moratorios.",
            "Los pagos después de la fecha límite pueden generar cargos adicionales.",
            "Conserve este documento como comprobante de su préstamo y los pagos realizados."
        ];
        notas.forEach(n => {
            pdf.text(`• ${n}`, margin + 2, y);
            y += 5;
        });
        y += 2;
        pdf.line(margin, y, pageWidth - margin, y);
        
        // Verificar si hay espacio suficiente para la sección de firmas
        if (y + 50 > pageHeight - margin - 20) {
            pdf.addPage();
            y = margin + 10;
        } else {
            y += 10;
        }

        // Sección de firmas mejorada
        // Ajustamos posición base
const firmaBaseY = y + 10; // Base común

// Sección de firmas (ajustada)
const lineWidth = 80;
pdf.setFontSize(9);
pdf.setFont("helvetica", "bold");

// Línea firma cliente
const leftStartX = centerX - lineWidth - 10;
const leftEndX = centerX - 10;
pdf.line(leftStartX, firmaBaseY + 10, leftEndX, firmaBaseY + 10);

// Línea firma aval
const rightStartX = centerX + 10;
const rightEndX = centerX + lineWidth + 10;
pdf.line(rightStartX, firmaBaseY + 10, rightEndX, firmaBaseY + 10);

// Texto firma cliente
pdf.setFontSize(8);
pdf.setFont("helvetica", "normal");

pdf.text("FIRMA DEL CLIENTE", leftStartX + (lineWidth - pdf.getTextWidth("FIRMA DEL CLIENTE")) / 2, firmaBaseY);
pdf.text(`${cliente.nombre || ""}`, leftStartX + (lineWidth - pdf.getTextWidth(cliente.nombre || "")) / 2, firmaBaseY + 15);
pdf.text(`${cliente.dni || ""}`, leftStartX + (lineWidth - pdf.getTextWidth(cliente.dni || "")) / 2, firmaBaseY + 20);

// Texto firma aval
pdf.text("FIRMA DEL AVAL", rightStartX + (lineWidth - pdf.getTextWidth("FIRMA DEL AVAL")) / 2, firmaBaseY);
pdf.text(`${cliente.recomendado || ""}`, rightStartX + (lineWidth - pdf.getTextWidth(cliente.recomendado || "")) / 2, firmaBaseY + 15);
pdf.text(`${cliente.Dnirecomendado || ""}`, rightStartX + (lineWidth - pdf.getTextWidth(cliente.Dnirecomendado || "")) / 2, firmaBaseY + 20);

// Línea y texto de firma adicional
const adicionalBaseY = firmaBaseY + 40; // MÁS ABAJO

const middleLineWidth = 100;
const middleStartX = centerX - middleLineWidth / 2;
const middleEndX = centerX + middleLineWidth / 2;
pdf.line(middleStartX, adicionalBaseY, middleEndX, adicionalBaseY);

const middleText = "FIRMA ADICIONAL / AUTORIZACIÓN";
pdf.text(middleText, centerX - pdf.getTextWidth(middleText) / 2, adicionalBaseY - 10);

const middleText2 = cliente.usuario.username;
pdf.text(middleText2, centerX - pdf.getTextWidth(middleText2) / 2, adicionalBaseY + 7);

const middleText1 = cliente.usuario.name;
pdf.text(middleText1, centerX - pdf.getTextWidth(middleText1) / 2, adicionalBaseY + 14);

const dniText = cliente.usuario.dni;
pdf.text(dniText, centerX - pdf.getTextWidth(dniText) / 2, adicionalBaseY + 21);


        // Añadir código QR con el número de referencia (si disponible la librería)
        try {
            if (typeof QRCode !== 'undefined') {
                const qrCodeDataUrl = await new Promise((resolve) => {
                    QRCode.toDataURL(referenceNumber, { width: 100, height: 100 }, (err, url) => {
                        resolve(err ? null : url);
                    });
                });
                
                if (qrCodeDataUrl) {
                    pdf.addImage(qrCodeDataUrl, 'PNG', centerX - 15, y + 55, 30, 30);
                    pdf.setFontSize(7);
                    pdf.text("Verificación", centerX, y + 90, { align: "center" });
                }
            }
        } catch (e) {
            console.log("QR Code generation not available");
        }

        // Agregar marca de agua y pie de página
        addWatermark("ORIGINAL");

        // Agregar pie de página a todas las páginas
        const totalPages = pdf.internal.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            addFooter(i, totalPages);
        }

        // Generar URL del PDF
        localPdfUrl.value = pdf.output("bloburl");
        
        console.log("PDF generado correctamente:", referenceNumber);
        return { success: true, referenceNumber };
    } catch (error) {
        console.error("Error al generar PDF:", error);
        return { success: false, error: error.message };
    }
};

onMounted(async () => {
    if (props.prestamosId) {
        await cargarDatosPrestamo();
    }
});

watch(() => props.prestamosId, async (newId) => {
    if (newId) {
        await cargarDatosPrestamo();
    }
});
</script>