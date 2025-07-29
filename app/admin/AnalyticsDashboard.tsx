'use client';

import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  BarChart, 
  Bar, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip, 
  ResponsiveContainer,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell
} from 'recharts';
import { 
  TrendingUp, 
  TrendingDown, 
  DollarSign, 
  ShoppingCart, 
  Package, 
  Users, 
  Download,
  FileText,
  Calendar,
  Filter
} from 'lucide-react';

interface AnalyticsData {
  summary: {
    total_orders: number;
    total_revenue: number;
    pending_orders: number;
    completed_orders: number;
    cancelled_orders: number;
    refunded_orders: number;
    average_order_value: number;
    total_discounts: number;
    total_taxes: number;
    conversion_rate: number;
    cancellation_rate: number;
    refund_rate: number;
  };
  payment_methods: Array<{
    gateway_name: string;
    payment_method: string;
    usage_count: number;
    total_amount: number;
    avg_amount: number;
  }>;
  top_books: Array<{
    title: string;
    author: string;
    sales_count: number;
    revenue: number;
  }>;
  customer_analytics: Array<{
    user: {
      id: number;
      name: string;
      email: string;
    };
    order_count: number;
    total_spent: number;
    avg_order_value: number;
  }>;
  date_range: {
    start_date: string;
    end_date: string;
  };
}

interface DeliveryAnalyticsData {
  summary: {
    total_deliveries: number;
    successful_deliveries: number;
    failed_deliveries: number;
    pending_deliveries: number;
    delivery_success_rate: number;
    average_delivery_time: number;
    total_downloads: number;
    unique_downloads: number;
    average_download_time: number;
    total_download_size: number;
  };
  top_downloaded_books: Array<{
    title: string;
    author: string;
    download_count: number;
  }>;
  delivery_performance: Array<{
    hour: number;
    total_deliveries: number;
    successful_deliveries: number;
    success_rate: number;
  }>;
  date_range: {
    start_date: string;
    end_date: string;
  };
}

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8'];

export default function AnalyticsDashboard() {
  const [analyticsData, setAnalyticsData] = useState<AnalyticsData | null>(null);
  const [deliveryData, setDeliveryData] = useState<DeliveryAnalyticsData | null>(null);
  const [loading, setLoading] = useState(true);
  const [dateRange, setDateRange] = useState('30');
  const [startDate, setStartDate] = useState<string>('');
  const [endDate, setEndDate] = useState<string>('');
  const [activeTab, setActiveTab] = useState('overview');

  useEffect(() => {
    fetchAnalytics();
  }, [dateRange, startDate, endDate]);

  const fetchAnalytics = async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (startDate && endDate) {
        params.append('start_date', startDate);
        params.append('end_date', endDate);
      } else {
        params.append('date_range', dateRange);
      }

      // Fetch purchase analytics
      const analyticsResponse = await fetch(`/api/v1/admin/analytics/purchase?${params}`);
      const analytics = await analyticsResponse.json();
      if (analytics.success) {
        setAnalyticsData(analytics.data);
      }

      // Fetch delivery analytics
      const deliveryResponse = await fetch(`/api/v1/admin/analytics/delivery?${params}`);
      const delivery = await deliveryResponse.json();
      if (delivery.success) {
        setDeliveryData(delivery.data);
      }
    } catch (error) {
      console.error('Error fetching analytics:', error);
    } finally {
      setLoading(false);
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(amount);
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">Analytics Dashboard</h1>
          <p className="text-gray-600">Comprehensive insights into sales and delivery performance</p>
        </div>
        <div className="flex items-center space-x-4">
          <Select value={dateRange} onValueChange={setDateRange}>
            <SelectTrigger className="w-32">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="7">Last 7 days</SelectItem>
              <SelectItem value="30">Last 30 days</SelectItem>
              <SelectItem value="90">Last 90 days</SelectItem>
              <SelectItem value="365">Last year</SelectItem>
            </SelectContent>
          </Select>
          <Button onClick={fetchAnalytics} variant="outline">
            <Filter className="w-4 h-4 mr-2" />
            Refresh
          </Button>
        </div>
      </div>

      {/* Date Range Picker */}
      <div className="flex items-center space-x-4">
        <div className="flex items-center space-x-2">
          <Calendar className="w-4 h-4" />
          <span className="text-sm font-medium">Custom Date Range:</span>
        </div>
        <input
          type="date"
          value={startDate}
          onChange={(e) => setStartDate(e.target.value)}
          className="border rounded px-3 py-1"
        />
        <span>to</span>
        <input
          type="date"
          value={endDate}
          onChange={(e) => setEndDate(e.target.value)}
          className="border rounded px-3 py-1"
        />
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab}>
        <TabsList>
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="sales">Sales Analytics</TabsTrigger>
          <TabsTrigger value="delivery">Delivery Analytics</TabsTrigger>
          <TabsTrigger value="customers">Customer Analytics</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-6">
          {/* Key Metrics */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
                <DollarSign className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">
                  {analyticsData ? formatCurrency(analyticsData.summary.total_revenue) : '$0'}
                </div>
                <p className="text-xs text-muted-foreground">
                  {analyticsData?.summary.completed_orders || 0} completed orders
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
                <ShoppingCart className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">
                  {analyticsData?.summary.total_orders || 0}
                </div>
                <p className="text-xs text-muted-foreground">
                  {analyticsData?.summary.conversion_rate || 0}% conversion rate
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Deliveries</CardTitle>
                <Package className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">
                  {deliveryData?.summary.total_deliveries || 0}
                </div>
                <p className="text-xs text-muted-foreground">
                  {deliveryData?.summary.delivery_success_rate || 0}% success rate
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Downloads</CardTitle>
                <Download className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">
                  {deliveryData?.summary.total_downloads || 0}
                </div>
                <p className="text-xs text-muted-foreground">
                  {deliveryData?.summary.unique_downloads || 0} unique downloads
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Charts */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Revenue Trend</CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <BarChart data={analyticsData?.top_books || []}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="title" />
                    <YAxis />
                    <Tooltip formatter={(value) => formatCurrency(Number(value))} />
                    <Bar dataKey="revenue" fill="#8884d8" />
                  </BarChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Payment Methods</CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <PieChart>
                    <Pie
                      data={analyticsData?.payment_methods || []}
                      cx="50%"
                      cy="50%"
                      labelLine={false}
                      label={({ gateway_name, total_amount }) => 
                        `${gateway_name}: ${formatCurrency(total_amount)}`
                      }
                      outerRadius={80}
                      fill="#8884d8"
                      dataKey="total_amount"
                    >
                      {analyticsData?.payment_methods.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                      ))}
                    </Pie>
                    <Tooltip formatter={(value) => formatCurrency(Number(value))} />
                  </PieChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="sales" className="space-y-6">
          {/* Sales Metrics */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Order Status</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span>Completed</span>
                    <Badge variant="default">{analyticsData?.summary.completed_orders || 0}</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Pending</span>
                    <Badge variant="secondary">{analyticsData?.summary.pending_orders || 0}</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Cancelled</span>
                    <Badge variant="destructive">{analyticsData?.summary.cancelled_orders || 0}</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Refunded</span>
                    <Badge variant="outline">{analyticsData?.summary.refunded_orders || 0}</Badge>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Financial Summary</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span>Total Revenue</span>
                    <span className="font-bold">{formatCurrency(analyticsData?.summary.total_revenue || 0)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Total Discounts</span>
                    <span>{formatCurrency(analyticsData?.summary.total_discounts || 0)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Total Taxes</span>
                    <span>{formatCurrency(analyticsData?.summary.total_taxes || 0)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Avg Order Value</span>
                    <span className="font-bold">{formatCurrency(analyticsData?.summary.average_order_value || 0)}</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Performance Rates</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span>Conversion Rate</span>
                    <Badge variant="default">{analyticsData?.summary.conversion_rate || 0}%</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Cancellation Rate</span>
                    <Badge variant="destructive">{analyticsData?.summary.cancellation_rate || 0}%</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Refund Rate</span>
                    <Badge variant="outline">{analyticsData?.summary.refund_rate || 0}%</Badge>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Top Selling Books */}
          <Card>
            <CardHeader>
              <CardTitle>Top Selling Books</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {analyticsData?.top_books.slice(0, 10).map((book, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded">
                    <div>
                      <h4 className="font-medium">{book.title}</h4>
                      <p className="text-sm text-gray-600">by {book.author}</p>
                    </div>
                    <div className="text-right">
                      <div className="font-bold">{formatCurrency(book.revenue)}</div>
                      <div className="text-sm text-gray-600">{book.sales_count} sales</div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="delivery" className="space-y-6">
          {/* Delivery Metrics */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Delivery Performance</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="flex justify-between">
                    <span>Total Deliveries</span>
                    <span className="font-bold">{deliveryData?.summary.total_deliveries || 0}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Successful</span>
                    <Badge variant="default">{deliveryData?.summary.successful_deliveries || 0}</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Failed</span>
                    <Badge variant="destructive">{deliveryData?.summary.failed_deliveries || 0}</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Pending</span>
                    <Badge variant="secondary">{deliveryData?.summary.pending_deliveries || 0}</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Success Rate</span>
                    <Badge variant="default">{deliveryData?.summary.delivery_success_rate || 0}%</Badge>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Download Statistics</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="flex justify-between">
                    <span>Total Downloads</span>
                    <span className="font-bold">{deliveryData?.summary.total_downloads || 0}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Unique Downloads</span>
                    <span>{deliveryData?.summary.unique_downloads || 0}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Total Size</span>
                    <span>{formatFileSize(deliveryData?.summary.total_download_size || 0)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Avg Download Time</span>
                    <span>{deliveryData?.summary.average_download_time || 0}s</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Avg Delivery Time</span>
                    <span>{deliveryData?.summary.average_delivery_time || 0}s</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Top Downloaded Books */}
          <Card>
            <CardHeader>
              <CardTitle>Top Downloaded Books</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {deliveryData?.top_downloaded_books.slice(0, 10).map((book, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded">
                    <div>
                      <h4 className="font-medium">{book.title}</h4>
                      <p className="text-sm text-gray-600">by {book.author}</p>
                    </div>
                    <div className="text-right">
                      <div className="font-bold">{book.download_count}</div>
                      <div className="text-sm text-gray-600">downloads</div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="customers" className="space-y-6">
          {/* Customer Analytics */}
          <Card>
            <CardHeader>
              <CardTitle>Top Customers</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {analyticsData?.customer_analytics.slice(0, 10).map((customer, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded">
                    <div>
                      <h4 className="font-medium">{customer.user.name}</h4>
                      <p className="text-sm text-gray-600">{customer.user.email}</p>
                    </div>
                    <div className="text-right">
                      <div className="font-bold">{formatCurrency(customer.total_spent)}</div>
                      <div className="text-sm text-gray-600">
                        {customer.order_count} orders • {formatCurrency(customer.avg_order_value)} avg
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
} 