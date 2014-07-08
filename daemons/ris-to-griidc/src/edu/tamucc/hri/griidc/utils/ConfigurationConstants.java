package edu.tamucc.hri.griidc.utils;

public class ConfigurationConstants {

	public ConfigurationConstants() {
		// TODO Auto-generated constructor stub
	}
	public static final int Unknown = -1;
	public static final int Undefined = Unknown;
	public static final int NotFound = Unknown;
	public static final String RefBaseXmlFileName = "refBase.xml";
	public static final String RefBaseXsdFileName = "refBase.xsd";

	public static final String DefaultStringListSeparator = ", ";
	public static final String AuthorListSeparator = "; ";
	
	public static final int EarliestPublicationYear = 1950;
	public static final int LatestPublicationYear = 2050;
	
	//if the file is an xml file this is the contents of the first line
	public static final String FirstLineOfXmlFile = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
	//
	// XML data value constants
	// These are values in the xml which are used as type markers.
	// They are obtained by looking (manually - with your eyes) at the
	// xml data. They are not expressed or limited in the schema xsd file
	//
	// Name and NamePart constants
	public static final String NameTypePersonal = "personal";
	public static final String NamePartTypeFamily = "family";
	public static final String NamePartTypeGiven = "given";
	//  Identifier type constants
	public static final String IdentifierTypeDoi = "doi";
	
	//  Genre authority marker
	public static final String GenreAuthority  = "marcgt";

	public static final int[] GoodPublicationSerialNumbers = { 2693, 2537,
			1010, 776 };
	public static final int[] BadPublicationSerialNumbers = { 10, 2899 };

	public static final int[] AllPubSerialNumbersAsOf_06_01_2014 = { 13, 14,
			17, 18, 22, 23, 28, 29, 34, 35, 39, 41, 42, 43, 55, 94, 95, 97, 98,
			99, 101, 103, 104, 105, 286, 288, 289, 291, 296, 299, 302, 305,
			306, 309, 312, 554, 674, 768, 770, 771, 772, 774, 775, 776, 791,
			792, 828, 829, 831, 832, 835, 867, 869, 870, 871, 872, 1009, 1010,
			1011, 1016, 1019, 1020, 1021, 1124, 1125, 1130, 1131, 1132, 1133,
			1134, 1135, 1136, 1138, 1139, 1140, 1141, 1142, 1144, 1181, 1193,
			1195, 1274, 1276, 1278, 1282, 1334, 1350, 1357, 1363, 1366, 1379,
			1381, 1383, 1384, 1395, 1396, 1397, 1398, 1400, 1401, 1403, 1424,
			1502, 1503, 1507, 1511, 1512, 1558, 1621, 1635, 1636, 1647, 1665,
			1685, 1708, 1709, 1723, 1738, 1741, 1742, 1743, 1744, 1745, 1746,
			1747, 1748, 1749, 1762, 1801, 1814, 1961, 2068, 2069, 2073, 2082,
			2083, 2085, 2086, 2088, 2096, 2098, 2103, 2107, 2108, 2109, 2110,
			2111, 2121, 2191, 2192, 2193, 2194, 2195, 2196, 2198, 2200, 2201,
			2255, 2257, 2260, 2261, 2262, 2263, 2264, 2266, 2267, 2268, 2269,
			2270, 2271, 2272, 2273, 2274, 2279, 2325, 2326, 2327, 2330, 2333,
			2334, 2335, 2336, 2343, 2344, 2345, 2368, 2373, 2375, 2419, 2422,
			2423, 2438, 2445, 2449, 2450, 2452, 2453, 2454, 2455, 2456, 2457,
			2458, 2459, 2460, 2461, 2462, 2463, 2464, 2465, 2468, 2469, 2470,
			2471, 2472, 2473, 2474, 2475, 2476, 2478, 2480, 2482, 2485, 2487,
			2488, 2491, 2492, 2493, 2496, 2498, 2501, 2531, 2537, 2538, 2569,
			2577, 2579, 2580, 2581, 2617, 2619, 2620, 2621, 2622, 2623, 2625,
			2626, 2632, 2634, 2635, 2638, 2686, 2693, 2791, 2836, 2837, 2862 };

	
	
	public static String getDefaultStringListSeparator() {
		return DefaultStringListSeparator;
		
	}
}
