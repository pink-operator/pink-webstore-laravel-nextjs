import { NextRequest, NextResponse } from 'next/server';

// The API URL to proxy to - ensure this points to the Laravel backend
const API_URL = 'http://localhost:8000/api';

export async function GET(
  request: NextRequest,
  { params }: { params: { path: string[] } }
) {
  const path = params.path.join('/');
  const { searchParams } = new URL(request.url);
  
  // Build the query string
  const queryString = searchParams.toString();
  const url = `${API_URL}/${path}${queryString ? `?${queryString}` : ''}`;
  
  // Forward the request to the API server
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      // Forward authorization header if present
      ...(request.headers.get('Authorization') 
        ? { 'Authorization': request.headers.get('Authorization') as string } 
        : {})
    },
    method: 'GET',
  });
  
  // Get the response data
  const data = await response.json();

  // Return the response
  return NextResponse.json(data, {
    status: response.status,
  });
}

export async function POST(
  request: NextRequest,
  { params }: { params: { path: string[] } }
) {
  const path = params.path.join('/');
  const url = `${API_URL}/${path}`;
  
  // Get the request body
  const body = await request.json();
  
  // Forward the request to the API server
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      // Forward authorization header if present
      ...(request.headers.get('Authorization') 
        ? { 'Authorization': request.headers.get('Authorization') as string } 
        : {})
    },
    body: JSON.stringify(body),
  });
  
  // Get the response data
  const data = await response.json();

  // Return the response
  return NextResponse.json(data, {
    status: response.status,
  });
}

export async function PUT(
  request: NextRequest,
  { params }: { params: { path: string[] } }
) {
  const path = params.path.join('/');
  const url = `${API_URL}/${path}`;
  
  // Get the request body
  const body = await request.json();
  
  // Forward the request to the API server
  const response = await fetch(url, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      // Forward authorization header if present
      ...(request.headers.get('Authorization') 
        ? { 'Authorization': request.headers.get('Authorization') as string } 
        : {})
    },
    body: JSON.stringify(body),
  });
  
  // Get the response data
  const data = await response.json();

  // Return the response
  return NextResponse.json(data, {
    status: response.status,
  });
}

export async function DELETE(
  request: NextRequest,
  { params }: { params: { path: string[] } }
) {
  const path = params.path.join('/');
  const url = `${API_URL}/${path}`;
  
  // Forward the request to the API server
  const response = await fetch(url, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      // Forward authorization header if present
      ...(request.headers.get('Authorization') 
        ? { 'Authorization': request.headers.get('Authorization') as string } 
        : {})
    },
  });
  
  // For a successful DELETE that returns no content
  if (response.status === 204) {
    return new NextResponse(null, { status: 204 });
  }
  
  // Get the response data if there is any
  try {
    const data = await response.json();
    return NextResponse.json(data, {
      status: response.status,
    });
  } catch (error) {
    // If no JSON response, just return the status
    return new NextResponse(null, {
      status: response.status,
    });
  }
}