import { NextResponse } from 'next/server';

// Simple API test endpoint
export async function GET() {
  try {
    // Test connection to the Laravel API
    const response = await fetch('http://localhost:8000/api/products?featured=true', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      return NextResponse.json({
        success: false,
        message: `API connection failed with status: ${response.status}`,
        status: response.status
      });
    }
    
    const data = await response.json();
    
    return NextResponse.json({
      success: true,
      message: 'API connection successful',
      data: data
    });
  } catch (error) {
    console.error('API test error:', error);
    return NextResponse.json({
      success: false,
      message: 'API connection error',
      error: (error as Error).message
    }, { status: 500 });
  }
}