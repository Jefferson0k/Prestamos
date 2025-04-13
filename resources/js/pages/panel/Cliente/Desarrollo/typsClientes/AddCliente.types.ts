export interface ClienteForm {
    dni: string;
    nombre: string;
    apellidos: string;
    telefono: string;
    direccion: string;
    correo: string;
    centro_trabajo: string;
    foto: File | null;
}

export interface ClienteFormErrors {
    dni: string;
    nombre: string;
    apellidos: string;
    telefono: string;
    direccion: string;
    correo: string;
    centro_trabajo: string;
    foto: string;
}

export interface AddClienteEmits {
    (e: 'cliente-added'): void;
}