import { NextRequest, NextResponse } from 'next/server';

// Base URL for the Laravel API
const API_BASE_URL = 'http://127.0.0.1:8000/api';

export async function GET(request: NextRequest) {
  try {
    // Get URL information from the request
    const url = new URL(request.url);
    const path = url.pathname.replace('/api/proxy', '');
    const searchParams = new URLSearchParams(url.search);
    
    // Convert searchParams to object for logging
    const paramsObject: Record<string, string> = {};
    searchParams.forEach((value, key) => {
      paramsObject[key] = value;
    });
    
    // Construct the target URL
    const apiUrl = `${API_BASE_URL}${path || '/'}${url.search || ''}`;
    
    console.log(`Proxying GET request to: ${apiUrl}`, { 
      path, 
      params: paramsObject,
      fullUrl: apiUrl
    });
    
    // Forward the request
    const response = await fetch(apiUrl, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      console.error(`API returned ${response.status}:`, await response.text());
      return NextResponse.json(
        { message: `API Error: ${response.status} ${response.statusText}` },
        { status: response.status }
      );
    }
    
    // Parse response data
    const data = await response.json();
    
    // Return the response
    return NextResponse.json(data);
  } catch (error) {
    console.error('Proxy error:', error);
    return NextResponse.json(
      { message: 'API Proxy Error', error: (error as Error).message },
      { status: 500 }
    );
  }
}

export async function POST(request: NextRequest) {
  try {
    const url = new URL(request.url);
    const path = url.pathname.replace('/api/proxy', '');
    
    // Get request body
    const body = await request.json();
    
    // Construct the full URL to the Laravel API
    const apiUrl = `${API_BASE_URL}${path || '/'}`;
    
    console.log(`Proxying POST request to: ${apiUrl}`);
    
    // Forward the request
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(body)
    });
    
    // Get the response data
    const data = await response.json();
    
    // Return the response
    return NextResponse.json(data, { status: response.status });
  } catch (error) {
    console.error('Proxy error:', error);
    return NextResponse.json(
      { message: 'API Proxy Error', error: (error as Error).message },
      { status: 500 }
    );
  }
}