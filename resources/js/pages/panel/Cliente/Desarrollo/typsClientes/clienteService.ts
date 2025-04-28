// src/services/clienteService.ts
import axios from 'axios';
import { Cliente } from './typesCliente';

interface PaginationParams {
  page: number;
  perPage: number;
  search?: string;
  estadoCliente?: string;
  sortBy?: string;
  sortDirection?: 'asc' | 'desc';
}

interface ApiResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
}

export const clienteService = {
  async getClientes(params: PaginationParams): Promise<ApiResponse<Cliente>> {
    try {
      const response = await axios.get('/cliente', {
        params: {
          page: params.page + 1, // API pagination is 1-based, but table is 0-based
          per_page: params.perPage,
          search: params.search,
          estado_cliente: params.estadoCliente,
          sort_by: params.sortBy,
          sort_direction: params.sortDirection
        }
      });
      
      return response.data;
    } catch (error) {
      console.error('Error fetching clients:', error);
      throw error;
    }
  },
  
  async createCliente(cliente: Partial<Cliente>): Promise<Cliente> {
    try {
      const response = await axios.post('/cliente', cliente);
      return response.data.data;
    } catch (error) {
      console.error('Error creating client:', error);
      throw error;
    }
  },
  
  async updateCliente(id: number, cliente: Partial<Cliente>): Promise<Cliente> {
    try {
      const response = await axios.put(`/cliente/${id}`, cliente);
      return response.data.data;
    } catch (error) {
      console.error('Error updating client:', error);
      throw error;
    }
  },
  
async deleteCliente(id: number): Promise<void> {
  try {
    await axios.delete(`/cliente/${id}`);
  } catch (error) {
    console.error('Error deleting client:', error);
    throw error;
  }
},
  
  async exportClientes(params?: Partial<PaginationParams>): Promise<Blob> {
    try {
      const response = await axios.get('/cliente/export', {
        params,
        responseType: 'blob'
      });
      return response.data;
    } catch (error) {
      console.error('Error exporting clients:', error);
      throw error;
    }
  }
};