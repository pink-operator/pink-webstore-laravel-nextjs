import axios from 'axios';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
const BASE_URL = API_URL.replace(/\/api$/, ''); // Remove /api suffix for non-API endpoints

export const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Important for CORS with credentials
});

// Check if we're in a browser environment
const isBrowser = typeof window !== 'undefined';

// Add request interceptor for authentication
apiClient.interceptors.request.use(async (config) => {
  if (isBrowser) {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Get CSRF token from cookie if needed
    const getCookie = (name: string) => {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop()?.split(';').shift();
    };
    
    const xsrfToken = getCookie('XSRF-TOKEN');
    if (xsrfToken) {
      config.headers['X-XSRF-TOKEN'] = xsrfToken;
    } else {
      // Get CSRF token if we don't have it
      try {
        console.log('Fetching CSRF token from:', `${BASE_URL}/sanctum/csrf-cookie`);
        const response = await axios.get(`${BASE_URL}/sanctum/csrf-cookie`, { 
          withCredentials: true,
          headers: {
            'Accept': 'application/json',
          },
        });
        console.log('CSRF token response:', response);
      } catch (error: any) {
        console.error('Failed to fetch CSRF token:', error);
        console.error('Error details:', {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status,
          headers: error.response?.headers,
        });
      }
    }
  }

  // Log the final request configuration
  console.log('Request config:', {
    url: config.url,
    method: config.method,
    headers: config.headers,
    withCredentials: config.withCredentials,
  });

  return config;
});

// Add response interceptor for error handling
apiClient.interceptors.response.use(
  (response) => {
    console.log('Response:', {
      status: response.status,
      headers: response.headers,
      data: response.data,
    });
    return response;
  },
  async (error) => {
    console.error('API Error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status,
      headers: error.response?.headers,
    });

    if (error.response?.status === 401 && isBrowser) {
      localStorage.removeItem('token');
      window.location.href = '/auth/login';
    }
    return Promise.reject(error);
  }
);