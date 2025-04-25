import { z } from 'zod';

export const userSchema = z.object({
  dni: z
    .number({ invalid_type_error: "DNI debe ser un número" })
    .int("El DNI debe ser un número entero")
    .positive("El DNI debe ser positivo")
    .min(10000000, "El DNI debe tener 8 dígitos")
    .max(99999999, "El DNI debe tener 8 dígitos"),
  name: z.string().min(1, "El nombre es obligatorio"),
  lastname: z.string().min(1, "El apellido es obligatorio"),
  birthdate: z.string().min(1, "La fecha de nacimiento es obligatoria"),
  email: z.string().email("Debe ser un correo válido"),
  username: z.string().min(1, "El usuario es obligatorio"),
  password: z.string().min(6, "La contraseña debe tener al menos 6 caracteres"),
  status: z.enum(["Activo", "Inactivo"], { required_error: "El estado es obligatorio" }),
});
