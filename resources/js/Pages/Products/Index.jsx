import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Layout from '@/Layouts/Layout';

export default function ProductsIndex({ products = [], status, message }) {
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [productList, setProductList] = useState(products);

    useEffect(() => {
        if (status === 'success') {
            setProductList(products);
            setError(null);
        } else {
            setError(message || 'Failed to load products');
        }
        setIsLoading(false);
    }, [products, status, message]);

    if (isLoading) {
        return (
            <Layout>
                <div className="py-12">
                    <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div className="flex justify-center items-center h-64">
                            <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-gray-900"></div>
                        </div>
                    </div>
                </div>
            </Layout>
        );
    }

    return (
        <Layout>
            <Head title="Products" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h1 className="text-3xl font-bold text-gray-900 mb-8">Our Products</h1>
                            
                            {error && (
                                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                                    <span className="block sm:inline">{error}</span>
                                </div>
                            )}

                            {!error && productList.length === 0 ? (
                                <div className="text-center py-12">
                                    <p className="text-gray-500">No products available at the moment.</p>
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                    {productList.map((product) => (
                                        <div key={product.id} className="border rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                                            <div className="h-48 bg-gray-100 overflow-hidden">
                                                <img 
                                                    src={product.image} 
                                                    alt={product.title} 
                                                    className="w-full h-full object-cover"
                                                    onError={(e) => {
                                                        e.target.onerror = null;
                                                        e.target.src = 'https://via.placeholder.com/300x300?text=No+Image';
                                                    }}
                                                />
                                            </div>
                                            <div className="p-4">
                                                <h3 className="font-semibold text-lg mb-2 line-clamp-2" title={product.title}>
                                                    {product.title}
                                                </h3>
                                                <p className="text-gray-600 text-sm mb-3 line-clamp-3">
                                                    {product.description}
                                                </p>
                                                <div className="flex justify-between items-center">
                                                    <span className="font-bold text-lg text-gray-900">
                                                        ${product.price.toFixed(2)}
                                                    </span>
                                                    <span className="text-sm bg-gray-100 px-2 py-1 rounded">
                                                        {product.category}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}
